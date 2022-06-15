<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class EmployeeDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_employee_documents';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function notes(){
        return $this->hasMany(EmployeeDocumentNote::class, 'company_employee_document_id', 'id');
    }
}
