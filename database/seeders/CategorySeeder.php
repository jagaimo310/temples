<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         DB::table('categories')->insert([
                'name'=>'街並み',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'都市',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'社寺',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'自然風景',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'スキー場',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'農山村地',
         ]);
         
         DB::table('categories')->insert([
                'name'=>'温泉',
         ]);
    }
}
