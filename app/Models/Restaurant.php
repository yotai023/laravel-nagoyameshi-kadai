<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegularHoliday;
use App\Models\Category;
use App\Models\Review;
use App\Models\Reservation;
use Kyslik\ColumnSortable\Sortable;

class Restaurant extends Model
{
    use HasFactory, Sortable;

    // ソート可能なカラムを指定
    public $sortable = [
        'created_at',
        'lowest_price'
    ];

    public function scopeRatingSortable($query)
    {
        return $query
            ->withAvg('reviews', 'score')
            ->orderBy('reviews_avg_score', 'desc');
    }


    protected $fillable = [
        'name',
        'description',
        'lowest_price',
        'highest_price',
        'postal_code',
        'address',
        'opening_time',
        'closing_time',
        'seating_capacity',
        'image'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant', 'restaurant_id', 'category_id');
    }

    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class, 'regular_holiday_restaurant', 'restaurant_id', 'regular_holiday_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function ratingSortable($query, $direction)
    {
        return $query->withAvg('reviews', 'score')
            ->orderBy('reviews_avg_score', $direction);
    }

    public function scopePopularSortable($query)
    {
        return $query
            ->withCount('reservations')
            ->orderBy('reservations_count', 'desc');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function favorite_restaurants()
    {
        return $this->belongsToMany(User::class, 'restaurant_user', 'user_id', 'restaurant_id')
            ->withTimestamps();
    }
}
