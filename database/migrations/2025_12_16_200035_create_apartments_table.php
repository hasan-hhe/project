<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // محاولة DISCARD tablespace وحذف الجدول
        try {
            // التحقق من وجود الجدول
            $result = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'apartments'");
            if ($result[0]->count > 0) {
                // محاولة DISCARD tablespace
                try {
                    DB::statement('ALTER TABLE apartments DISCARD TABLESPACE');
                } catch (\Exception $e) {
                    // تجاهل الخطأ
                }
                // حذف الجدول
                DB::statement('DROP TABLE apartments');
            }
        } catch (\Exception $e) {
            // إذا فشل، جرب حذف مباشر
            try {
                DB::statement('DROP TABLE IF EXISTS apartments');
            } catch (\Exception $e2) {
                // تجاهل الخطأ
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // إنشاء الجدول باستخدام SQL مباشرة لتجنب مشاكل tablespace
        DB::statement("
            CREATE TABLE IF NOT EXISTS apartments (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                owner_id BIGINT UNSIGNED NOT NULL,
                governorate_id BIGINT UNSIGNED NOT NULL,
                city_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(8, 2) NOT NULL,
                rooms_count INT NOT NULL,
                address_line VARCHAR(255) NOT NULL,
                rating_avg DECIMAL(8, 2) NOT NULL DEFAULT 5,
                is_active TINYINT(1) NOT NULL DEFAULT 0,
                is_favorite TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                INDEX idx_owner_id (owner_id),
                INDEX idx_governorate_id (governorate_id),
                INDEX idx_city_id (city_id),
                FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (governorate_id) REFERENCES governorates(id) ON DELETE CASCADE,
                FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
