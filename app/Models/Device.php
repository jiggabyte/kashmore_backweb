<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Device extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id', 'username', 'token', 'state', 'created_at', 'updated_at'
    ];
}
