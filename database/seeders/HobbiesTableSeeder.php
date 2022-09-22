<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hobbies;

class HobbiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $data = ['Reading','Travelling','Dancing','Singing','Shopping','Blogging'];

        foreach($data as $value) {
            Hobbies::create([
                'name' => $value
            ]);
        }
    }
}
