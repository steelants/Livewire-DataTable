<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\Support;

/**
 * Vysledek hromadne akce - kolik radku se zpracovalo, preskocilo a kolik spadlo.
 *
 * Preskocene radky se daji seskupit podle duvodu, aby uzivatel videl nejen
 * "4 preskoceny", ale i proc.
 */
class BulkResult
{
    /**
     * @param array<string,int> $reasons Pocty preskocenych radku podle duvodu.
     */
    public function __construct(
        public readonly int $ok = 0,
        public readonly int $skipped = 0,
        public readonly int $failed = 0,
        public readonly array $reasons = [],
    ) {
    }

    public function total(): int
    {
        return $this->ok + $this->skipped + $this->failed;
    }

    public function isEmpty(): bool
    {
        return $this->total() === 0;
    }

    /**
     * Neco se nepovedlo - bud preskoceno, nebo spadlo.
     */
    public function hasProblems(): bool
    {
        return $this->skipped > 0 || $this->failed > 0;
    }

    /**
     * Duvody preskoceni serazene od nejcastejsiho.
     *
     * @return array<string,int>
     */
    public function sortedReasons(): array
    {
        $reasons = $this->reasons;
        arsort($reasons);

        return $reasons;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'ok'      => $this->ok,
            'skipped' => $this->skipped,
            'failed'  => $this->failed,
            'total'   => $this->total(),
            'reasons' => $this->reasons,
        ];
    }
}
