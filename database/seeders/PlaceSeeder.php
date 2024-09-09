<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use DateTime;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         
         DB::table('places')->insert([
                'prefecture'=>'京都',
                'area'=>'下京区',
         ]);
         
         DB::table('places')->insert([
                'prefecture'=>'神奈川',
                'area'=>'鎌倉市',
         ]);
         
         DB::table('places')->insert([
                'prefecture'=>'京都',
                'area'=>'伏見区',
         ]);
         
         DB::table('places')->insert([
                'prefecture'=>'奈良',
                'area'=>'奈良市',
         ]);
         
         DB::table('places')->insert([
                'prefecture'=>'広島',
                'area'=>'廿日市市',
         ]);
         
    }
}
