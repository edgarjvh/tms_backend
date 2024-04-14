<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $table = 'widgets';
    protected $fillable = ['top', 'left'];

    public function user_codes()
    {
        $this->belongsToMany(UserCode::class)->withPivot(['top', 'left']);
    }
}
