<?php

namespace Tests\Fixtures;

/**
 * renderRow() replaces the casts and the renderColumnX() methods of the parent.
 */
class MarkersRowComponent extends MarkersActionsComponent
{
    public function renderRow(array $row): array
    {
        return [
            'id'     => '<strong>' . e($row['id']) . '</strong>',
            'name'   => e($row['name']),
            'email'  => e($row['email']),
            'note'   => e($row['note']),
            'status' => strtoupper(e($row['status'])),
            'title'  => e($row['title']),
            'active' => $row['active'] ? 'yes' : 'no',
            'score'  => e($row['score']),
        ];
    }
}
