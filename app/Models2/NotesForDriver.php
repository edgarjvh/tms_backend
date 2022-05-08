<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class NotesForDriver extends Model
{
    protected array $guarded = [];
    protected string $table = 'order_notes_for_driver';
}
