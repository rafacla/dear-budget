<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banks')->insertOrIgnore([
            ['short_name' => 'Itaú', 'full_name' => 'Banco Itaú S.A.', 'country' => 'Brazil', 'icon' => 'brazil_itau.svg'],
            ['short_name' => 'Santander', 'full_name' => 'Banco Santander S.A.', 'country' => 'Brazil', 'icon' => 'brazil_santander.svg'],
            ['short_name' => 'BTG+', 'full_name' => 'Banco BTG Pactual S.A.', 'country' => 'Brazil', 'icon' => 'brazil_btgmais.svg'],
            ['short_name' => 'BTG Pactual Digital', 'full_name' => 'Banco BTG Pactual S.A.', 'country' => 'Brazil', 'icon' => 'brazil_btgdigital.svg'],
            ['short_name' => 'XP Investimentos', 'full_name' => 'XP Investimentos CVM', 'country' => 'Brazil', 'icon' => 'brazil_xpi.svg'],
            ['short_name' => 'Rico', 'full_name' => 'Rico Investimentos', 'country' => 'Brazil', 'icon' => 'brazil_rico.svg'],
            ['short_name' => 'Banco Inter', 'full_name' => 'Banco Inter S.A.', 'country' => 'Brazil', 'icon' => 'brazil_inter.svg'],
        ]);
    }
}
