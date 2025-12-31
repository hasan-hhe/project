<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            'دمشق',
            'ريف دمشق',
            'حلب',
            'حمص',
            'حماة',
            'اللاذقية',
            'طرطوس',
            'دير الزور',
            'الحسكة',
            'الرقة',
            'إدلب',
            'السويداء',
            'درعا',
            'القنيطرة',
        ];

        foreach ($governorates as $name) {
            Governorate::create([
                'name' => $name,
            ]);
        }
    }
}
