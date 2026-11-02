<?php

use Livewire\Livewire;
use Tests\Fixtures\Post;
use Tests\Fixtures\PostRenderComponent;
use Tests\Fixtures\RenderComponent;
use Tests\Fixtures\RenderRowComponent;

/**
 * Body cells of every rendered row, as raw HTML.
 */
function renderedCells(string $html): array
{
    preg_match_all('/<tr wire:key="row-[^"]+">(.*?)<\/tr>/s', $html, $rows);

    return array_map(function (string $row) {
        preg_match_all('/<td>(.*?)<\/td>/s', $row, $cells);

        return array_map('trim', $cells[1]);
    }, $rows[1]);
}

beforeEach(function () {
    RenderComponent::$rows = [
        ['id' => 1, 'name' => 'Jan', 'active' => true, 'note' => '<script>x</script>', 'raw' => '<b>bold</b>'],
        ['id' => 2, 'name' => 'Eva', 'active' => false, 'note' => 'plain', 'raw' => 'a & b'],
    ];
});

describe('array driver rendering', function () {
    it('escapes columns without a cast or render method', function () {
        $cells = renderedCells(Livewire::test(RenderComponent::class)->html());

        expect($cells[0][0])->toBe('1')
            ->and($cells[0][4])->toBe('&lt;b&gt;bold&lt;/b&gt;')
            ->and($cells[1][4])->toBe('a &amp; b');
    });

    it('renders casts with key, value and the row', function () {
        $cells = renderedCells(Livewire::test(RenderComponent::class)->html());

        expect($cells[0][2])->toBe('<span class="cast-active-yes">#1</span>')
            ->and($cells[1][2])->toBe('<span class="cast-active-no">#2</span>');
    });

    it('prefers the render cast over renderColumnX() on the same column', function () {
        $cells = renderedCells(Livewire::test(RenderComponent::class)->html());

        expect($cells[0][1])->toBe('<span class="cast-name-yes">#1</span>');
    });

    it('renders renderColumnX() output unescaped with access to the row', function () {
        $cells = renderedCells(Livewire::test(RenderComponent::class)->html());

        expect($cells[0][3])->toBe('<em data-id="1">&lt;script&gt;x&lt;/script&gt;</em>')
            ->and($cells[1][3])->toBe('<em data-id="2">plain</em>');
    });

    it('lets renderRow() replace casts and column methods for the whole row', function () {
        $cells = renderedCells(Livewire::test(RenderRowComponent::class)->html());

        expect($cells)->toBe([
            ['<b>1</b>', 'Jan', 'on', '&lt;script&gt;x&lt;/script&gt;', '&lt;b&gt;bold&lt;/b&gt;'],
            ['<b>2</b>', 'Eva', 'off', 'plain', 'a &amp; b'],
        ]);
    });
});

describe('database driver rendering', function () {
    it('renders casts, render methods and escaped columns', function () {
        Post::insert([
            ['title' => 'A <b>', 'score' => 0, 'published' => true],
            ['title' => 'B', 'score' => 0, 'published' => false],
        ]);

        expect(renderedCells(Livewire::test(PostRenderComponent::class)->html()))->toBe([
            ['1', '<u>A &lt;b&gt;</u>', '<span class="cast-published-yes">#1</span>'],
            ['2', '<u>B</u>', '<span class="cast-published-no">#2</span>'],
        ]);
    });
});
