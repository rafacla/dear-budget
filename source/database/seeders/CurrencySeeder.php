<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insertOrIgnore([
            ['name' => 'Real', 'symbol' => 'R$', 'code' => 'BRL', 'decimalPlaces' => 2,'active' => true, 'default' => true],
            ['name' => 'Dollar (US)', 'symbol' => '$', 'code' => 'USD', 'decimalPlaces' => 2,'active' => true, 'default' => false],
            ['name' => 'Euro', 'symbol' => '€', 'code' => 'EUR', 'decimalPlaces' => 2,'active' => true, 'default' => false],            
        ]);
    }
}
