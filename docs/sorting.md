# Sorting

Sorting is enabled by default. Set `$sortable = true` and optionally restrict which columns are sortable via `$sortableColumns`.

## Simple columns

Sorting by any direct column (string, int, bool) works out of the box:

```php
public bool $sortable = true;
public array $sortableColumns = ['name', 'score', 'published'];
```

## BelongsTo relation

Use dot notation — the package resolves the join automatically:

```php
// headers
'user.name' => 'User'

// sortBy
$sortBy = 'user.name';
```

## HasMany / MorphMany — sort by count

Same dot notation. The package detects the relation type and generates a COUNT subquery:

```php
// headers
'comments.id' => 'Comments'  // sorts by number of comments
'reactions.id' => 'Reactions' // sorts by number of reactions (morph-aware)

// sortBy
$sortBy = 'comments.id';
```

## Custom sort expression

Override `orderColumn{Name}()` to return a raw SQL expression:

```php
public function orderColumnName(): string
{
    return 'LOWER(name)';
}
```
