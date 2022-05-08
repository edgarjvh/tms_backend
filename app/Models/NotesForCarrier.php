<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class NotesForCarrier extends Model
{
    protected $guarded = [];
    protected $table = 'order_notes_for_carrier';
}
