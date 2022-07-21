<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplateInternalNotes extends Model
{
    protected $guarded = [];
    protected $table = 'template_order_internal_notes';
}
