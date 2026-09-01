# Content migration patterns (Sulu 3.0)

Verified against sulu/sulu 3.0: the content package's `MetadataLoader` maps
`templateKey` (string, indexed) and the JSON columns `templateData`, `seoData`,
`excerptData` onto every dimension content entity; the core packages name their tables
as below.

## Tables and dimension columns

| Content | Table |
| --- | --- |
| pages | `pa_page_dimension_contents` |
| articles | `ar_article_dimension_contents` |
| snippets | `sn_snippet_dimension_contents` |
| custom entities | own table, e.g. `event_dimension_contents` |

Each row is one dimension: `locale`, `stage` (`draft` or `live`), `version` (`0` =
current, snapshots count up), `templateKey`. A migration that should affect "the
content" must therefore update all rows of an entity, not one.

## Property rename

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename property "teaser" to "intro" in the event page template';
    }

    public function up(Schema $schema): void
    {
        $this->transform('teaser', 'intro');
    }

    public function down(Schema $schema): void
    {
        $this->transform('intro', 'teaser');
    }

    private function transform(string $from, string $to): void
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, templateData FROM pa_page_dimension_contents WHERE templateKey = ?',
            ['event'],
        );

        foreach ($rows as $row) {
            $data = \json_decode($row['templateData'], true, 512, \JSON_THROW_ON_ERROR);

            if (!\array_key_exists($from, $data)) {
                continue;
            }

            $data[$to] = $data[$from];
            unset($data[$from]);

            $this->connection->update(
                'pa_page_dimension_contents',
                ['templateData' => \json_encode($data, \JSON_THROW_ON_ERROR)],
                ['id' => $row['id']],
            );
        }
    }
}
```

Direct `$this->connection` calls execute immediately (unlike `addSql`, which is queued)
- that is fine for data migrations and the only way to transform in PHP. For large
tables replace the single `fetchAllAssociative` with an id-based batch loop
(`WHERE templateKey = ? AND id > ? ORDER BY id LIMIT 500`).

## Block restructuring

Blocks are stored under their property name as an array of objects; each entry carries
its `type` plus that type's properties (for nested blocks see the next section).
Renaming a block type and moving a field:

```php
$data = \json_decode($row['templateData'], true, 512, \JSON_THROW_ON_ERROR);
$changed = false;

foreach ($data['blocks'] ?? [] as $i => $block) {
    if ('text' !== ($block['type'] ?? null)) {
        continue;
    }

    $block['type'] = 'editor';
    $block['article'] = $block['text'] ?? null;
    unset($block['text']);

    $data['blocks'][$i] = $block;
    $changed = true;
}

if ($changed) {
    // update as in the rename pattern
}
```

The same loop deletes obsolete block types (collect indexes, remove, reindex the array
with `array_values`) or splits one type into two.

## Nested blocks

A block type can carry another block property; the stored JSON then nests the same
structure one level deeper:

```json
{
    "blocks": [
        {
            "type": "columns",
            "columnBlocks": [
                {"type": "text", "text": "..."},
                {"type": "image", "image": {"id": 5}}
            ]
        }
    ]
}
```

A flat `foreach` over `$data['blocks']` misses those inner entries. Transform
recursively instead; when the template is known, recurse into the nested block
properties by name, otherwise detect them generically (a list of arrays that carry a
`type` key):

```php
/**
 * @param array<int, array<string, mixed>> $blocks
 *
 * @return array<int, array<string, mixed>>
 */
private function transformBlocks(array $blocks): array
{
    foreach ($blocks as $i => $block) {
        if ('text' === ($block['type'] ?? null)) {
            $block['type'] = 'editor';
            $block['article'] = $block['text'] ?? null;
            unset($block['text']);
        }

        foreach ($block as $key => $value) {
            if (\is_array($value) && \array_is_list($value) && isset($value[0]['type'])) {
                $block[$key] = $this->transformBlocks($value);
            }
        }

        $blocks[$i] = $block;
    }

    return $blocks;
}
```

Called as `$data['blocks'] = $this->transformBlocks($data['blocks'] ?? []);` compare
against the original array (`$data !== $original`) to decide whether the row needs an
update.

## SQL-only variant for trivial renames

On MySQL/MariaDB a flat property rename works without PHP, via `addSql`:

```sql
UPDATE pa_page_dimension_contents
SET templateData = JSON_REMOVE(
    JSON_SET(templateData, '$.intro', JSON_EXTRACT(templateData, '$.teaser')),
    '$.teaser'
)
WHERE templateKey = 'event'
  AND JSON_CONTAINS_PATH(templateData, 'one', '$.teaser');
```

Database-specific and hard to review for anything nested - prefer the PHP pattern as
soon as blocks are involved.

## SEO and excerpt data

`seoData` and `excerptData` are their own JSON columns with fixed shapes (the
SEO/excerpt tab fields). Moving a value between the content tab and one of these tabs is
a move between columns:

```php
$template = \json_decode($row['templateData'], true, 512, \JSON_THROW_ON_ERROR);
$seo = \json_decode($row['seoData'], true, 512, \JSON_THROW_ON_ERROR);

$seo['description'] = $template['metaDescription'] ?? $seo['description'] ?? null;
unset($template['metaDescription']);
```

## After migrating

```bash
bin/console doctrine:migrations:migrate
bin/adminconsole cache:clear && bin/websiteconsole cache:clear
bin/adminconsole cmsig:seal:reindex   # when searchable/listed data changed
```
