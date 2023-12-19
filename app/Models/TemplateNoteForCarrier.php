<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplateNoteForCarrier extends Model
{
    protected $guarded = [];
    protected $table = 'template_order_notes_for_carrier';

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}
