<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable =
    [
        'id',
        'order_number',
        'outlet_id',
        'delivery_date',
        'delivery_slot_id',
        'delivery_status', // 0 khali, 1 dispatched 2 delivered 2
        'delivery_trip_type',  // regular = 0, return = 1
        'membership_number',
        'pos_bill',
        'payment_method',   // 0 = cod , 1 = online
        'ca_remarks',
        'availablity_status',
        'delivery_executive_id',
        'user_id',
        'delivered_date',
        'delivery_time',
        'attachment',
        'de_remarks',
        'last_update_user_id',
        'edit_blocked',
        'addtional_remarks',
        'follow_up_pending'
    ];
}
