<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCodeWidget extends Model
{
    protected $table = 'user_code_widget';
    protected $fillable = [
        'user_code_id',
        'widget_id',
        'top',
        'left'
    ];

    public function user_codes()
    {
        $this->belongsToMany(UserCode::class);
    }

    public function widgets()
    {
        $this->belongsToMany(Widget::class);
    }
}
