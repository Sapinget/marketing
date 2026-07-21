# AI Workspace

Workspace ini adalah playbook lintas model untuk Claude, Codex, OpenCode, dan agent AI lain. Tujuannya adalah memberi struktur kerja yang sama untuk 7 crew bisnis tanpa mengunci ke satu vendor atau model.

## Crew

| Crew | Mission | Output utama |
| --- | --- | --- |
| Developers | Ship code faster, from scaffold to QA | Code, tests, review notes, release checklist |
| Design | UI that never looks templated | UI direction, components, visual QA |
| Marketing | Copy, SEO and ads that convert | SEO audits, ad concepts, CRO experiments |
| Social & Content | Feed the algorithm on autopilot | Content pillars, calendars, posts, email sequences |
| Finance | Model the number before you spend | Pricing, forecasts, DCF, comps, pitch materials |
| Operations | Run the business like a machine | SOPs, runbooks, postmortems, internal comms |
| Legal | Read the fine print to you | Contract notes, risk summaries, compliance checks |

## Routing

Use `ai-workspace/crew-manifest.json` as the structured source of truth. Use `ai-workspace/skills.md` for skill routing, and the markdown playbooks in `ai-workspace/crews/` when the agent needs richer instructions.

Default routing:

- Code, bug, QA, app architecture: Developers.
- Visual product, dashboard, component, brand system: Design.
- SEO, ads, landing page copy, conversion: Marketing.
- Social media, video, newsletter, content calendar: Social & Content.
- Pricing, budgets, model, fundraising numbers: Finance.
- SOP, launch, incident, process, internal updates: Operations.
- Contract, NDA, compliance, policy, legal risk: Legal.

## Operating Rules

1. Pick one lead crew and optional supporting crews before starting.
2. Convert vague requests into a concrete brief: goal, audience, constraints, deliverable, quality bar.
3. Prefer repo-local facts over assumptions. Inspect existing files before editing code or docs.
4. Keep artifacts small and composable. Add new files only when they become reusable.
5. Never hardcode secrets. Treat credentials, tokens, customer data, and private financial data as sensitive.
6. For code work, write or update tests when behavior changes and run the smallest useful verification.
7. For legal and finance work, produce decision support, not professional advice.

## How To Use

Ask an agent:

```text
Use the AI Workspace. Route this to the best crew:
<your task>
```

For multi-crew work:

```text
Use Developers as lead, with Design and Marketing support:
<your task>
```

For repeatable tasks, copy the structure from `ai-workspace/task-template.md` and create a task file in `ai-workspace/tasks/`.

