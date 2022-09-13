<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PermissionUserCode extends Pivot
{
    protected array $guarded = [];

    public function user_code()
    {
        return $this->belongsTo(UserCode::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
