<?php

use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BaseModel::unguard();

        $listCity = [
            ['id' => 78, 'country_id' => 1, 'name' => 'Санкт-Петербург', 'short' => 'СПб'],
            ['id' => 77, 'country_id' => 1, 'name' => 'Москва', 'short' => 'Мск'],
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('city')->truncate();

        foreach ($listCity as $row) {
            Model\City::create(array_merge($dataBase, $row));
        }
    }
}
