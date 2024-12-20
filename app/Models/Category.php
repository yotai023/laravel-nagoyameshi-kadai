<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * 店舗との関連付け
     */
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class);
    }
}
