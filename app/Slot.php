<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $fillable = [
        'slot_name',
        'slot_time',
        'slot_start',
        'slot_time',
    ];
}
