<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplateInternalNote extends Model
{
    protected $guarded = [];
    protected $table = 'template_order_internal_notes';

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}
