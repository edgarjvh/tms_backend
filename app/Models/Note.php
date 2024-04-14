<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Note
 *
 * Represents a note in the system.
 *
 * @package App\Models
 */

class Note extends Model
{
    protected $guarded = [];
    protected $table = 'customer_notes';

    /**
     * Get the customer that belongs to this instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user code that belongs to this instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}
