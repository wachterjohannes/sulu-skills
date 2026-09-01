# sulu-skills

Sulu 3.0 best practices, written as skills for AI coding agents, so that generated Sulu
code looks like Sulu code - the tasks the
[sulu-demo example PRs](https://github.com/sulu/sulu-demo/pulls) demonstrate, encoded as
reusable, verified how-tos.

Structure follows [wachterjohannes/symfony-skills](https://github.com/wachterjohannes/symfony-skills):
skills live under `skills/<name>/SKILL.md`, `AGENTS.md` indexes them with their triggers
for agents without a native skill system, and there is no install magic - copy the folders.
Two deliberate divergences: skill names keep the `sulu-` prefix (a skill called `template`
collides too easily once copied next to other skill sets), and skills may carry a
`references/` folder - Sulu tasks involve irreducible XML/PHP structure that a 40-line
skill body cannot hold, and references load only on demand.

Distribution stays raw for now; later possibly as a
[Symfony AI Mate](https://github.com/sulu/sulu-mate-extension) extension so a Sulu project
pulls the skills in via Composer.

See [BRAINSTORM.md](BRAINSTORM.md) for the full idea list and rationale.

## Skills

| Skill | Status | Scope |
| --- | --- | --- |
| [sulu-discover](skills/sulu-discover/) | ✅ draft | Look it up in the project instead of recalling it - files, debug commands, tags |
| [sulu-template](skills/sulu-template/) | ✅ draft | Page/snippet/article templates: XML + Twig + registration |
| [sulu-custom-entity](skills/sulu-custom-entity/) | ✅ draft | Custom Doctrine entity in the admin (list, form, API, permissions) |
| [sulu-content-entity](skills/sulu-content-entity/) | ✅ draft | Equip a custom entity with the content system (templates, workflow, preview, website) |
| [sulu-block](skills/sulu-block/) | 📝 planned | Block types incl. block settings |
| [sulu-extend-entity](skills/sulu-extend-entity/) | 📝 planned | Extend built-in entities (contact, category, user, excerpt) |
| [sulu-content-type](skills/sulu-content-type/) | 📝 planned | Custom content types and admin field types |
| [sulu-list](skills/sulu-list/) | 📝 planned | List transformers, toolbar/item actions, overlay adapters |
| [sulu-admin-ui](skills/sulu-admin-ui/) | 📝 planned | Admin JS build: custom views, styling, CKEditor, icons |
| [sulu-website](skills/sulu-website/) | 📝 planned | Navigation, smart content, search, sitemap, robots.txt |
| [sulu-webspace](skills/sulu-webspace/) | 📝 planned | Webspaces, localizations, portals, redirects |
| [sulu-headless](skills/sulu-headless/) | 📝 planned | Headless delivery / APIs |
| [sulu-form](skills/sulu-form/) | 📝 planned | SuluFormBundle dynamic forms |
| [sulu-testing](skills/sulu-testing/) | 📝 planned | Functional, Pest, E2E and API tests |
| [sulu-devops](skills/sulu-devops/) | 📝 planned | Setup, Docker/FrankenPHP, deployment |
| [sulu-upgrade](skills/sulu-upgrade/) | ✅ draft | 2.6 → 3.0 migration and 3.x updates |
| [sulu-content-migration](skills/sulu-content-migration/) | ✅ draft | Content migrations for structural changes (property renames, blocks, bulk edits) |
| [sulu-doctor](skills/sulu-doctor/) | 📝 planned | Diagnose common misconfigurations |

Planned skills carry a `README.md` describing their intended scope; a skill is real once
it has a `SKILL.md`.

## Installing

Copy the folders. There is no install script.

**Claude Code**, per project:

```bash
mkdir -p .claude/skills
cp -R /path/to/sulu-skills/skills/* .claude/skills/
```

(or `~/.claude/skills/` for every project).

**Codex**, per project:

```bash
mkdir -p .agents/skills
cp -R /path/to/sulu-skills/skills/* .agents/skills/
```

(or `~/.agents/skills/` for every project).

**opencode** picks up the Claude Code and Codex locations (`.claude/skills/`,
`.agents/skills/` and their global counterparts) as-is; its native locations are
`.opencode/skills/` per project and `~/.config/opencode/skills/` globally.

Other agents: point them at `AGENTS.md`, which indexes every skill with its
trigger - [docs/other-agents.md](docs/other-agents.md) shows an example table to
paste into the project's instructions file.

## Conventions

- Target version is **Sulu 3.0**. Guidance is verified against the
  [sulu/skeleton `3.0` branch](https://github.com/sulu/skeleton/tree/3.0) and the
  [sulu/sulu `3.0` branch](https://github.com/sulu/sulu/tree/3.0) - 2.x-only advice does
  not belong here, and every skill's frontmatter states its `sulu-versions` constraint.
- The sulu-demo example PRs are the *task catalog*, not copy sources - their diffs are
  2.x and must be re-derived for 3.0.
- `SKILL.md` stays short and imperative; long verified background goes to `references/`,
  copy-paste starting points to `templates/`.
- Prefer pointing at existing tooling (`sulu-rector`, `doctrine:migrations`, the debug
  commands) over embedding what the tooling already generates.
