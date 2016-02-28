<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as BaseModel;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BaseModel::unguard();

        $listColor = [
            ['name' => 'Белый', 'code' => 'fff'],
            ['name' => 'Желтый', 'code' => 'ff0'],
            ['name' => 'Оранжевый', 'code' => 'fb940b'],
            ['name' => 'Розовый', 'code' => 'ff98bf'],
            ['name' => 'Красный', 'code' => 'c00'],
            ['name' => 'Зеленый', 'code' => '0c0'],
            ['name' => 'Голубой', 'code' => '03c0c6'],
            ['name' => 'Синий', 'code' => '00f'],
            ['name' => 'Фиолетовый', 'code' => '762ca7'],
            ['name' => 'Коричневый', 'code' => '885418'],
            ['name' => 'Серый', 'code' => '999'],
            ['name' => 'Черный', 'code' => '000']
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('color')->truncate();

        $i = 0;
        foreach ($listColor as $row) {
            $row['sort'] = $i++;
            $row['id'] = $i;
            Model\Color::create(array_merge($dataBase, $row));
        }
    }
}
