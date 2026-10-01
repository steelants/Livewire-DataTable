<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Runs the same data pipeline as render(), without rendering the blade view.
 */
function tableData(SteelAnts\DataTable\Livewire\DataTableComponent $table): array
{
    return Closure::bind(fn () => $this->getData(), $table, SteelAnts\DataTable\Livewire\DataTableComponent::class)();
}

/**
 * Eight posts shared by the filtering, export and sorting tests.
 */
function seedPosts(): void
{
    Tests\Fixtures\Post::insert([
        ['title' => 'Jan Novák', 'score' => 30, 'published' => true, 'published_at' => '2024-01-10'],
        ['title' => 'Petr Svoboda', 'score' => 10, 'published' => true, 'published_at' => '2024-02-15'],
        ['title' => 'Jana Nováková', 'score' => 20, 'published' => true, 'published_at' => '2024-03-20'],
        ['title' => 'Karel Dvořák', 'score' => 10, 'published' => true, 'published_at' => '2024-04-25'],
        ['title' => '100% hotovo', 'score' => 5, 'published' => true, 'published_at' => null],
        ['title' => '100 hotovo', 'score' => 5, 'published' => true, 'published_at' => null],
        ['title' => 'a_b', 'score' => 1, 'published' => true, 'published_at' => null],
        ['title' => 'axb', 'score' => 1, 'published' => true, 'published_at' => null],
    ]);
}
