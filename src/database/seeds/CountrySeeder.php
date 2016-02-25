<?php

use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BaseModel::unguard();

        $listCountry = [
            ['id' => 1, 'name' => 'Россия', 'short' => 'РФ'],
            ['id' => 2, 'name' => 'Украина', 'short' => 'Укр'],
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('country')->truncate();

        foreach ($listCountry as $row) {
            Model\Country::create(array_merge($dataBase, $row));
        }
    }
}
