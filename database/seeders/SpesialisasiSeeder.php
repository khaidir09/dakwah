<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SpesialisasiSeeder extends Seeder
{

    protected $data = [
        [
            'name' => 'TAFSIR'
        ],
        [
            'name' => 'HADITS'
        ],
        [
            'name' => 'AKIDAH'
        ],
        [
            'name' => 'FIQIH'
        ],
        [
            'name' => 'TASAWUF'
        ],
        [
            'name' => 'AKHLAK'
        ],
        [
            'name' => 'SIRAH NABAWIYAH'
        ],
        [
            'name' => 'TARIKH ISLAM'
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $d) {
            DB::table('specializations')->insert([
                'name' => $d['name'],
            ]);
        }
    }
}
