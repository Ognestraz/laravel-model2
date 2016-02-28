<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as BaseModel;

class AgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BaseModel::unguard();

        $listAge = [
            ['id' => 1, 'name' => 'Ренессанс'],
            ['id' => 2, 'name' => 'Барокко'],
            ['id' => 3, 'name' => 'Рококо'],
            ['id' => 4, 'name' => 'Классицизм'],
            ['id' => 5, 'name' => 'Ампир'],
            ['id' => 6, 'name' => 'Реставрация'],
            ['id' => 7, 'name' => 'Бидермейер'],
            ['id' => 8, 'name' => 'Романтизм'],
            ['id' => 9, 'name' => 'Второе рококо'],
            ['id' => 10, 'name' => 'Викторианство'],
            ['id' => 11, 'name' => 'Турнюр'],
            ['id' => 12, 'name' => 'Модерн'],
            ['id' => 13, 'name' => 'Готика'],
            ['id' => 14, 'name' => 'Фэнтези']
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('age')->truncate();

        $i = 0;
        foreach ($listAge as $row) {
            $row['sort'] = $i++;
            Model\Age::create(array_merge($dataBase, $row));
        }
    }
}
