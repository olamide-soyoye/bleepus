<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'Professional', 'description' => 'Professionals in Bleepus'],
            ['name' => 'Business', 'description' => 'Businesses in Bleepus']
        ];

        // Insert the data into the 'posts' table
        foreach ($data as $item) {
            UserType::create($item);
        }
    }
}
