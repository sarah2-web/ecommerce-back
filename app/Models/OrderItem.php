<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
   protected $fillable = ['o_id', 'p_id', 'quantity', 'item_price', 'created_at', 'updated_at'];
   public function order(){
       return $this->belongsTo(Order::class, 'o_id');
   }
   public function product(){
       return $this->belongsTo(Product::class, 'p_id');
   }
}
