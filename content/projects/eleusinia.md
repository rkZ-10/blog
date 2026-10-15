---
name: Eleusinia (contributor)
tagline: Contributing voice reliability to a peer-to-peer, serverless Discord-style app for desktop.
stack: [TypeScript, Electron, WebRTC, Web Audio, HyperDHT]
repo_url: https://github.com/mmhfn1/Eleusinia
status: active
featured: true
sort_order: 3
---

Eleusinia is a desktop app for chat and voice calls with no central server, created by
[mmhfn1](https://github.com/mmhfn1). Peers find each other over a public DHT, and calls run as a full
WebRTC mesh. I'm a contributor, focused on making voice calls hold up the way people expect from Discord.

What I've added so far:

- **Calls that recover:** an ICE restart after a network blip, then rebuilds with backoff if that fails, with
  the decisions in a small unit-tested state module.
- **Devices that follow you:** if a headset is unplugged mid-call, the call moves to the default device, and
  it switches back when the headset returns, even under a new device id. The fallback relay also works on
  IPv6-only networks.
- **Speaking rings and per-person volume up to 200%,** through Web Audio.
- **Screen share picker:** separate Screens and Applications tabs, overlays filtered out, and a proper full
  screen toggle.
- **A test suite you can trust:** the end-to-end test moved onto a local test network so it passes every
  time, and it never captures anything real on the screen.

Work in progress: call sounds and audio settings are next.
