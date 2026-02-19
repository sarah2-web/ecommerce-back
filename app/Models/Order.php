<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
   protected $primaryKey = 'o_id';

protected $fillable = [
    'user_id',
    'total_price',
    'status',
    'order_date'
];
// Order.php
public function user() {
    return $this->belongsTo(User::class, 'user_id', 'id');
}
public function items()
{
    return $this->hasMany(OrderItem::class, 'o_id');
}
}
