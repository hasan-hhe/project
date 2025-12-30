<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مدير
        User::create([
            'first_name' => 'مدير',
            'last_name' => 'النظام',
            'phone_number' => '0999999999',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'account_type' => 'ADMIN',
            'date_of_birth' => '1990-01-01',
        ]);

        // إنشاء أصحاب شقق
        $owners = [
            ['أحمد', 'محمد', '0911111111', 'ahmed@example.com', 'PENDING'],
            ['فاطمة', 'علي', '0922222222', 'fatima@example.com', 'APPROVED'],
            ['خالد', 'حسن', '0933333333', 'khalid@example.com', 'APPROVED'],
            ['سارة', 'يوسف', '0944444444', 'sara@example.com', 'APPROVED'],
            ['محمد', 'أحمد', '0955555555', 'mohammed@example.com', 'REJECTED'],
        ];

        foreach ($owners as $owner) {
            User::create([
                'first_name' => $owner[0],
                'last_name' => $owner[1],
                'phone_number' => $owner[2],
                'email' => $owner[3],
                'password' => Hash::make('password'),
                'account_type' => 'OWNER',
                'status' => $owner[4],
                'date_of_birth' => Carbon::now()->subYears(25)->subDays(rand(0, 365))->format('Y-m-d'),
            ]);
        }

        // إنشاء مستأجرين
        $firstNames = ['أحمد', 'محمد', 'علي', 'حسن', 'خالد', 'يوسف', 'محمود', 'عمر', 'طارق', 'سامي', 'فاطمة', 'سارة', 'مريم', 'ليلى', 'نور', 'هدى', 'ريم', 'زينب', 'آمنة', 'خديجة'];
        $lastNames = ['محمد', 'أحمد', 'علي', 'حسن', 'يوسف', 'خالد', 'محمود', 'عمر', 'طارق', 'سامي', 'الزهراء', 'الرضا', 'الكاظم', 'الباقر', 'الصادق', 'النور', 'الهدى', 'الرحمة', 'البركة', 'الخير'];

        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'first_name' => $firstNames[array_rand($firstNames)],
                'last_name' => $lastNames[array_rand($lastNames)],
                'phone_number' => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'email' => 'renter' . $i . '@example.com',
                'password' => Hash::make('password'),
                'account_type' => 'RENTER',
                'date_of_birth' => Carbon::now()->subYears(30)->subDays(rand(0, 365))->format('Y-m-d'),
            ]);
        }
    }
}
