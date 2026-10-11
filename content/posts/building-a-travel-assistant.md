---
title: Building a travel assistant you can measure
project: travel-assistant
tags: [AI, MCP, RAG, Evals]
excerpt: An AI agent for Indian domestic flights that quotes live fares and official fare rules, and an eval suite that caught the bugs a demo never would.
published_at: 2026-10-11
---

Booking a domestic flight in India means juggling three things at once: live fares, each airline's
change and cancellation rules, and the DGCA's passenger-rights rules that sit on top of them. A plain
chatbot will happily answer all three, and it will be confidently wrong about the fees.

I wanted to see how far I could get with an assistant that only says what it can back up. Every price
comes from a live search, every rule comes from an official page with a date on it, and an automated
test suite checks that it keeps doing that. This post walks through how it's built and the three bugs
that taught me the most.

The code is on GitHub: [rkZ-10/travel-assistant](https://github.com/rkZ-10/travel-assistant).

![The chat UI answering "Cheapest flight to Chennai on Oct 15", with live tool activity and a fare table](/images/posts/travel-assistant/chat-answer.png)

## What it does

- **Searches live fares** for Indian domestic routes and applies saved preferences: home airport,
  nonstop only, a departure window, preferred and avoided airlines, a budget.
- **Answers fare-rule questions** ("what does cancelling an IndiGo fare cost 5 days out?") from
  official airline pages and DGCA rules, with the source and the date it was captured.
- **Shows flight cards** with a "See booking options" button. That lists the airline and travel sites
  selling the flight, and opens the seller's own page with the flight already selected. It never books
  or pays; you do that on the airline's site.
- **Checks today's flight status** through a separate API, for day-of-travel questions.

## How it's built

There are four layers, and each was tested before the next one was built on top of it.

```text
React chat UI ──WebSocket──▶ FastAPI backend ──▶ Agent (Claude Agent SDK + guardrails)
                                                      │ MCP
                                                      ▼
                         travel-mcp server: 9 tools, SQLite cache + quota counter
                           ├─ flight search  (SerpApi / Google Flights)
                           ├─ flight status  (AirLabs)
                           ├─ policy search  (RAG over airline + DGCA pages)
                           └─ preferences    (JSON file with change history)
```

**An MCP server owns everything that touches the outside world.** MCP (Model Context Protocol) is a
standard way to expose tools to AI apps. Because the agent can only reach the tools over MCP, the same
server also works inside Claude Desktop, and either side can be replaced without touching the other.

The flight APIs have small free quotas (about 250 searches a month), so every call goes through one
helper in a fixed order: check the cache, check the monthly budget, call the API, count the call, cache
the result. Inputs are validated before any quota is spent, and the agent is capped at four searches per
request. There's no way to burn the month's quota by accident.

**A RAG layer quotes the rules instead of guessing them.** 15 official sources (IndiGo, Air India,
Air India Express, Akasa, SpiceJet, Alliance Air and two DGCA regulations) are captured, split into
about 700 chunks and indexed in plain SQLite. Search is hybrid: FTS5 keyword ranking for exact terms
like "Flexi Plus" or "4,299", and local embeddings for paraphrases like "get my money back", with the two
rankings merged by reciprocal rank fusion. At this size a vector database adds nothing, so there isn't
one.

Some airline sites block automated clients, and one asks bots not to fetch its documents. Rather than
pretend to be a browser, those pages are saved by hand and indexed from the file, and the fetcher
respects robots.txt. Every passage carries its capture date, and anything older than 60 days comes with a
warning.

**The agent plans; code enforces the rules.** The agent runs on the Claude Agent SDK with no built-in
tools at all, only the MCP server. The important limits live in a hook that runs before every tool
call, not in the prompt: an allow-list of tools, the search cap, approval before changing saved
preferences, and a cost cap per request. A prompt is a suggestion; a hook is a rule.

**The web UI streams what the agent is doing.** FastAPI holds one agent session per WebSocket and
forwards each tool call as it happens ("Searching flights HYD → MAA… done in 6.5s"), then the cards and
the answer. Preferences and booking links are plain REST calls that go straight to the MCP server
without involving the model at all.

![Flight cards with fare expiry timers and booking options](/images/posts/travel-assistant/chat-cards.png)

## The evals are the part that mattered

An answer can read beautifully and still be wrong. The first manual run guessed a refund figure without
checking the DGCA rules, which is exactly the kind of mistake you don't notice while demoing.

So there's a suite of 23 test questions that run through the real agent and are scored automatically
from the saved trace: which tools it called, with which arguments, whether it cited a source and date,
and whether guardrails fired when they should. The most useful check is grounding: **every rupee amount
in the answer must appear in some tool output.** The only exception is a difference between two
amounts that are both quoted. No LLM judges anything; every check is deterministic and explainable.

The suite now passes 23 out of 23. Getting there was more interesting than the score.

## Three bugs worth telling

**The cheapest fare that wasn't.** In one real run the agent called IndiGo's UpFront fare "the cheapest
fare type" and quoted its ₹999 cancellation fee. UpFront is the most expensive fare; I checked on IndiGo's
own booking page, where it cost ₹8,865 against ₹5,977 for the basic fare. The agent had picked the fee
row with the lowest number. A Google Flights price is booked as a basic fare, where cancelling costs
₹4,299 or more. The fix was a rule (quote the basic fare type's row, never a premium one, and say "if
booked as Saver") and a new test question that fails if it happens again.

**The test that passed for the wrong reason.** After adding SpiceJet, its test passed because "3,999"
appeared in the answer. Reading the answer, the agent had actually said it couldn't find a domestic fee
and was quoting an unlabelled fragment it didn't trust. Two extraction bugs were behind it: SpiceJet's
Domestic and International tabs weren't recognised, so the table had no "Domestic" label, and the table
sat inside a bullet list and came out as loose lines. Earlier, the same page had extracted to zero
characters, because the site wraps its whole page in one HTML form and the extractor dropped forms as
booking widgets. All three are fixed, and the test now also fails if the answer says it couldn't find
the fee. Lesson: read passing answers too.

**The booking token the model couldn't copy.** Cards were first picked by Google's booking token, a long
opaque string. In a real run the model garbled one while copying it, and the backend correctly refused
to show a card for a flight that wasn't in the search results. The safety check worked, but the agent
had to retry. Cards are now picked by flight number ("6E 243"), which is much harder to get wrong, and
the backend still fills in the price, times and links from the real search result. The model never
states a price on a card.

## What I'd tell myself at the start

- Put guardrails in code, not in the prompt.
- Write evals early, and read the answers behind both the failures and the passes.
- Check the checker: the first two "failures" in the eval suite were bugs in the grounding check
  itself, which misread a JSON list like `[3050,6000]` as one number.
- Most of the work in RAG is extraction. A fee table that doesn't know whether it's domestic or
  international produces a confident, wrong answer.

Next on the list: the 2026 revision of the DGCA refund rules, a couple of smaller airlines, and hosting
the tools as a remote MCP connector so other people can use them inside their own Claude.
