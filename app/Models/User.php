<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Symfony\Component\HttpKernel\Profiler\Profile;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Profile;
use App\Models\Order;
use App\Models\OrderItem;
class User extends Authenticatable
{
    use HasApiTokens ,HasFactory, Notifiable;
    //
    protected $fillable = ['name','email','password','phone','address','avatar','birthdate','remember_token'];
    protected $hidden = ['password','remember_token'];
    public function orders(){
        return $this->hasMany(Order::class);
    }
    public function profile(){
        return $this->hasOne(Profile::class);
    }

}
