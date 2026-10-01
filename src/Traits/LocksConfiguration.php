<?php

namespace SteelAnts\DataTable\Traits;

use Livewire\Attributes\Locked;
use LogicException;
use ReflectionProperty;

/**
 * Makes sure the configuration properties stay #[Locked].
 *
 * Their values go into the query (column and relation names) or pick the view, so the
 * browser must not change them. #[Locked] is declared in DataTableComponent, but PHP
 * attributes are not inherited: a component that redeclares the property without
 * #[Locked] would silently lose the lock. Used by DataTableComponent - Livewire calls the
 * boot hook on every request, before updates from the browser are applied.
 */
trait LocksConfiguration
{
    /** @var array<class-string, true> classes already checked in this process */
    private static array $checkedLockedConfiguration = [];

    /**
     * @return list<string>
     */
    protected function lockedConfiguration(): array
    {
        return ['searchableColumns', 'sortableColumns', 'viewName'];
    }

    public function bootLocksConfiguration(): void
    {
        if (isset(self::$checkedLockedConfiguration[static::class])) {
            return;
        }

        foreach ($this->lockedConfiguration() as $property) {
            $reflection = new ReflectionProperty($this, $property);

            if (empty($reflection->getAttributes(Locked::class))) {
                throw new LogicException(sprintf(
                    '%s::$%s must be declared with #[Locked] - the browser could change it otherwise. '
                    . 'Add "#[Locked]" (use Livewire\Attributes\Locked) above the property.',
                    $reflection->getDeclaringClass()->getName(),
                    $property,
                ));
            }
        }

        self::$checkedLockedConfiguration[static::class] = true;
    }
}
