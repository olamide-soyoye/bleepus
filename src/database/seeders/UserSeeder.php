<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['fname' => 'Test1', 'lname' => 'User1', 'email' => 'tester@gmail.com', 'user_type_id' => '2', 'password' => Hash::make('12345678'), 'email_verified_at' => '2023-12-24 17:10:49' ],
            ['fname' => 'Test2 ', 'lname' => 'User2', 'email' => 'tester2@gmail.com', 'user_type_id' => '1', 'password' => Hash::make('12345678'), 'email_verified_at' => '2023-12-24 17:10:49' ]
        ];

        foreach ($data as $item) {
            User::create($item);
        }
    }
}
