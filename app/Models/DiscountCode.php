<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $table = 'discount_codes';


    public function user(){

        //return $this->belongsTo(User::class);
        return $this->belongsToMany(User::class, 'discount_code_user', 'discount_code_id', 'user_id')->withTimestamps();

    }
    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
