<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $table = 'reactions';
    public $timestamps = false;
    protected $fillable = ['reactable_id', 'reactable_type', 'type'];
}
