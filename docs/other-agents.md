# Using the skills with other agents

For agents without a native skill system: copy the `skills/` folders into the
project (any location works, e.g. `ai/skills/`) and add an index table to the
instructions file the agent reads every session (`AGENTS.md`, `GEMINI.md`,
`.cursorrules`, …). The table tells the agent when to open which skill — without
it, the copied folders are never read.

Example, with the skills copied to `ai/skills/`:

```markdown
## Sulu skills

Each file under `ai/skills/` covers one Sulu task. Read a skill when its trigger
applies; don't read them all up front, and pull a file from its `references/`
folder only when the skill points you at it.

| Skill | Read it when | File |
|---|---|---|
| sulu-discover | about to rely on memory for a Sulu API, template, webspace, route or service | ai/skills/sulu-discover/SKILL.md |
| sulu-template | creating or changing a page/snippet/article template (XML + Twig) | ai/skills/sulu-template/SKILL.md |
| sulu-custom-entity | a project entity needs list/form/API in the Sulu admin | ai/skills/sulu-custom-entity/SKILL.md |
| sulu-content-entity | a project entity needs page-like content: templates, SEO, publish workflow, preview | ai/skills/sulu-content-entity/SKILL.md |
| sulu-upgrade | updating Sulu versions, migrating 2.6 content to 3.0, or fixing an upgrade gone wrong | ai/skills/sulu-upgrade/SKILL.md |
```

This repo's own [AGENTS.md](../AGENTS.md) is the same table with repo-relative
paths — copying it over and adjusting the `File` column works too. Keep the table
in sync when adding or removing skills.
