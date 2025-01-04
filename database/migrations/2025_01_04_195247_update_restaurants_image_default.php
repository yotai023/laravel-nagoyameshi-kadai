<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateRestaurantsImageDefault extends Migration
{
    public function up()
    {
        DB::table('restaurants')
            ->whereNull('image')
            ->update(['image' => '']);
    }

    public function down()
    {
        DB::table('restaurants')
            ->where('image', '')
            ->update(['image' => null]);
    }
}