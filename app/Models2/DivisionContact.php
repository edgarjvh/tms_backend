<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */
class DivisionContact extends Model
{
    protected array $guarded = [];
    protected string $table = 'contacts';

    public function division(){
        return $this->belongsTo(Division::class)
            ->with(['contacts', 'notes', 'mailing_address', 'hours', 'documents']);
    }
}
