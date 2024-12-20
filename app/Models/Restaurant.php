<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegularHoliday;
use App\Models\Category;
use Kyslik\ColumnSortable\Sortable;

class Restaurant extends Model
{
    use HasFactory;

    use Sortable;

    // ソート可能なカラムを指定
    public $sortable = [
        'created_at',
        'lowest_price'
    ];

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

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class, 'regular_holiday_restaurant');
    }
    
}
