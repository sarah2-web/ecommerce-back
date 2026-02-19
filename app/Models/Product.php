<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    use HasFactory;
    protected $primaryKey = 'p_id';
    protected $fillable = ['name', 'description', 'price', 'image', 'category', 'size', 'stock'];

    public function ordersItems(){
        return $this->hasMany(OrderItem::class, 'p_id');
    }
}
