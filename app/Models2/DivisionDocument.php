<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * Post
 *
 * @mixin Builder
 */
class DivisionDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'division_documents';

    public function division(){
        return $this->belongsTo(Division::class)
            ->with(['contacts', 'notes', 'mailing_address', 'hours', 'documents']);;
    }

    public function notes(){
        return $this->hasMany(DivisionDocumentNote::class)->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}
