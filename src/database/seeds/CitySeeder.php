<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as BaseModel;

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
            ['id' => 63, 'country_id' => 1, 'name' => 'Самара', 'short' => 'Смр'],
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('city')->truncate();

        $i = 0;
        foreach ($listCity as $row) {
            $row['sort'] = $i++;
            Model\City::create(array_merge($dataBase, $row));
        }
    }
}
