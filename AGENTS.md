# Agent Instructions

This repo uses the AI Workspace in `ai-workspace/` for Claude, Codex, OpenCode, and other AI coding models.

## Default Workflow

1. Read `ai-workspace/README.md`.
2. Route the request to one lead crew from `ai-workspace/crew-manifest.json`.
3. Use supporting crews only when the task crosses domains.
4. Inspect existing repo context before editing.
5. Keep changes scoped and verify them.

## Crews

- Developers: code, tests, QA, MCP, skill creation, memory.
- Design: UI, UX, visual artifacts, canvas, interaction polish.
- Marketing: SEO, CRO, ads, landing copy, campaign psychology.
- Social & Content: posts, scripts, content calendar, email sequences.
- Finance: pricing, forecasts, DCF, comps, pitch materials.
- Operations: SOPs, runbooks, incidents, internal communication, XLSX.
- Legal: contracts, NDA, legal risk, compliance, document review.

## Safety

Never hardcode secrets. Validate inputs at boundaries. For legal and finance tasks, provide decision support rather than final professional advice.

