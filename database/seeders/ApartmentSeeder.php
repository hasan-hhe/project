<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owners = User::where('account_type', 'OWNER')
            ->where('status', 'APPROVED')
            ->get();

        $cities = City::all();

        if ($owners->isEmpty() || $cities->isEmpty()) {
            $this->command->warn('لا توجد أصحاب شقق موافق عليهم أو مدن. قم بتشغيل UserSeeder و CitySeeder أولاً.');
            return;
        }

        $apartments = [
            ['شقة فاخرة في قلب المدينة', 'شقة واسعة ومريحة مع إطلالة رائعة', 50000, 3],
            ['شقة عصرية حديثة', 'شقة أنيقة مع جميع الخدمات', 75000, 4],
            ['شقة هادئة في حي راقي', 'شقة مريحة في موقع ممتاز', 60000, 2],
            ['شقة فاخرة مع حديقة', 'شقة كبيرة مع حديقة خاصة', 90000, 5],
            ['شقة اقتصادية', 'شقة نظيفة ومناسبة للعائلات', 40000, 2],
            ['شقة حديثة التصميم', 'شقة عصرية مع ديكور عصري', 65000, 3],
            ['شقة فاخرة مع إطلالة', 'شقة واسعة مع إطلالة جميلة', 80000, 4],
            ['شقة مريحة', 'شقة هادئة ومناسبة', 55000, 3],
            ['شقة أنيقة', 'شقة جميلة مع جميع الخدمات', 70000, 4],
            ['شقة عائلية', 'شقة واسعة مناسبة للعائلات الكبيرة', 85000, 5],
        ];

        $streetNames = ['الجامع', 'الرئيسي', 'الحرية', 'الاستقلال', 'الكرامة', 'الشهداء', 'النهضة', 'الوحدة'];

        foreach ($apartments as $index => $apartment) {
            $owner = $owners->random();
            $city = $cities->random();
            $governorate = $city->governorate;

            Apartment::create([
                'owner_id' => $owner->id,
                'governorate_id' => $governorate->id,
                'city_id' => $city->id,
                'title' => $apartment[0],
                'description' => $apartment[1],
                'price' => $apartment[2],
                'rooms_count' => $apartment[3],
                'address_line' => 'شارع ' . $streetNames[array_rand($streetNames)] . ' - ' . $city->name,
                'rating_avg' => round(rand(35, 50) / 10, 1),
                'is_active' => rand(1, 100) <= 80, // 80% نشطة
                'is_favorite' => false,
            ]);
        }
    }
}
