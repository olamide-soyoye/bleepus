<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['user_id' => 'Test1', 'profile_id' => '', 'company_name' => 'Test Business', 'max_distance' => '3', 'ratings' => '4' ],
        ];

        foreach ($data as $item) {
            Business::create($item);
        }
    }
}
