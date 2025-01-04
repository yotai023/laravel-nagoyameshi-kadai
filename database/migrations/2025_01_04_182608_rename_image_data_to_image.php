<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 使用原始 SQL 语句先删除 image 列
        DB::statement('ALTER TABLE restaurants DROP COLUMN image');
        
        // 然后重命名 image_data 为 image
        DB::statement('ALTER TABLE restaurants CHANGE image_data image LONGTEXT NULL');
    }

    public function down(): void
    {
        // 回滚时先将 image 重命名回 image_data
        DB::statement('ALTER TABLE restaurants CHANGE image image_data LONGTEXT NULL');
        
        // 然后重新创建 image 列
        DB::statement('ALTER TABLE restaurants ADD COLUMN image VARCHAR(255) NULL');
    }
};
