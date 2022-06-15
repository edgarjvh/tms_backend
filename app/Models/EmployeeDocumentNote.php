<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class EmployeeDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_employee_document_notes';

    public function document(){
        return $this->belongsTo(EmployeeDocument::class);
    }
}
