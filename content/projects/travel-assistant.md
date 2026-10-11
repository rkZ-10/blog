---
name: Travel Assistant
tagline: An AI agent for Indian domestic flights with live fares, cited fare rules and a 23-case eval suite.
stack: [MCP, Hybrid RAG, Claude Agent SDK, FastAPI, React]
repo_url: https://github.com/rkZ-10/travel-assistant
status: active
featured: true
sort_order: 1
---

Planning an Indian domestic flight means juggling live fares, airline fare rules, passenger-rights
policy and personal preferences across several sites, and a plain chatbot will happily invent a fee or
a rule.

Travel Assistant is a 9-tool MCP server for live fare search (Google Flights via SerpApi), day-of-travel
status (AirLabs), saved preferences and policy search. A RAG layer over official IndiGo, Air India,
Air India Express, Akasa, SpiceJet, Alliance Air and DGCA pages uses hybrid retrieval (SQLite FTS5
plus local bge-small embeddings), so every rule comes back cited and freshness-checked. A Claude Agent
SDK planner ties it together with guardrails
enforced in code, and a React and FastAPI chat UI shows live tool activity and flight cards that
redirect to the airline's booking page. It never books or pays. The web UI uses your Claude login when
it finds one, otherwise your own Anthropic API key.

A 23-case eval suite scores tool use, arguments, citations and whether every ₹ amount came from a tool.
