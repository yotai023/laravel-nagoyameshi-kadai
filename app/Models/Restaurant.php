<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegularHoliday;
use App\Models\Category;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'lowest_price',
        'highest_price',
        'postal_code',
        'address',
        'opening_time',
        'closing_time',
        'seating_capacity',
    ];

    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
