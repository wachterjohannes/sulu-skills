# sulu-skills

Agent skills for **Sulu 3.0** project development: distilled, verified how-tos that let
an AI coding agent (e.g. Claude Code) perform common Sulu customizations correctly —
the tasks the [sulu-demo example PRs](https://github.com/sulu/sulu-demo/pulls)
demonstrate, encoded as reusable skills.

Skills are kept **raw and tool-agnostic**: one directory per skill with a `SKILL.md`
(instructions), `references/` (verified background material) and `templates/` (file
skeletons). No packaging for now; distribution later possibly as a
[Symfony AI Mate](https://github.com/sulu/sulu-mate-extension) extension so a Sulu
project pulls the skills in via Composer.

See [BRAINSTORM.md](BRAINSTORM.md) for the full idea list and rationale.

## Skills

| Skill | Status | Scope |
| --- | --- | --- |
| [sulu-template](sulu-template/) | ✅ draft | Page/snippet/article templates: XML + Twig + registration |
| [sulu-block](sulu-block/) | 📝 planned | Block types incl. block settings |
| [sulu-custom-entity](sulu-custom-entity/) | 📝 planned | Custom Doctrine entity in the admin (list, form, API, permissions) |
| [sulu-extend-entity](sulu-extend-entity/) | 📝 planned | Extend built-in entities (contact, category, user, excerpt) |
| [sulu-content-type](sulu-content-type/) | 📝 planned | Custom content types and admin field types |
| [sulu-list](sulu-list/) | 📝 planned | List transformers, toolbar/item actions, overlay adapters |
| [sulu-admin-ui](sulu-admin-ui/) | 📝 planned | Admin JS build: custom views, styling, CKEditor, icons |
| [sulu-website](sulu-website/) | 📝 planned | Navigation, smart content, search, sitemap, robots.txt |
| [sulu-webspace](sulu-webspace/) | 📝 planned | Webspaces, localizations, portals, redirects |
| [sulu-headless](sulu-headless/) | 📝 planned | Headless delivery / APIs |
| [sulu-form](sulu-form/) | 📝 planned | SuluFormBundle dynamic forms |
| [sulu-testing](sulu-testing/) | 📝 planned | Functional, Pest, E2E and API tests |
| [sulu-devops](sulu-devops/) | 📝 planned | Setup, Docker/FrankenPHP, deployment |
| [sulu-upgrade](sulu-upgrade/) | 📝 planned | 2.x → 3.0 and 3.x upgrades |
| [sulu-content-migration](sulu-content-migration/) | 📝 planned | Content migrations for structural changes |
| [sulu-doctor](sulu-doctor/) | 📝 planned | Diagnose common misconfigurations |

Planned skills carry a `README.md` describing their intended scope; a skill is real
once it has a `SKILL.md`.

## Conventions

- Target version is **Sulu 3.0**. Guidance is verified against the
  [sulu/skeleton `3.0` branch](https://github.com/sulu/skeleton/tree/3.0); 2.x-only
  advice does not belong here.
- The sulu-demo example PRs are the *task catalog*, not copy sources — their diffs are
  2.x and must be re-derived for 3.0.
- `SKILL.md` stays short and imperative; long background goes to `references/`,
  copy-paste starting points go to `templates/`.
