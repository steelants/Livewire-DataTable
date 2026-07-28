<?php

declare(strict_types=1);

namespace SteelAnts\DataTable\Support;

/**
 * Result of a bulk action - how many rows were processed, skipped, and how many failed.
 *
 * Skipped rows can be grouped by reason, so the user sees not just
 * "4 skipped", but also why.
 */
class BulkResult
{
    /**
     * @param array<string,int> $reasons Counts of skipped rows grouped by reason.
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
     * Something went wrong - either skipped or failed.
     */
    public function hasProblems(): bool
    {
        return $this->skipped > 0 || $this->failed > 0;
    }

    /**
     * Skip reasons sorted from most frequent.
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
