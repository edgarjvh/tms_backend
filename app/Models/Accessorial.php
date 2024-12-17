<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessorial extends Model
{
    protected $table = 'accessorials';
    protected $guarded = [];

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
