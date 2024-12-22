<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hazmat extends Model
{
    protected $table = 'hazmats';
    protected $guarded = [];

    public function hazmat_class(){
        return $this->hasOne(HazmatClass::class, 'id', 'hazmat_class_id');
    }
}
