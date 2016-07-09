<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as BaseModel;

class SizeSeeder extends Seeder
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
            ['label' => 'XXS (42-44)', 'name' => 'xxs', 'min' => 42, 'max' => 44],
            ['label' => 'XS (44-46)', 'name' => 'xs', 'min' => 44, 'max' => 46],
            ['label' => 'S (46-48)', 'name' => 's', 'min' => 46, 'max' => 46],
            ['label' => 'M (48-50)', 'name' => 'm', 'min' => 48, 'max' => 50],
            ['label' => 'L (50-52)', 'name' => 'l', 'min' => 50, 'max' => 52],
            ['label' => 'XL (52-54)', 'name' => 'xl', 'min' => 52, 'max' => 54],
            ['label' => 'XXL (54-56)', 'name' => 'xxl', 'min' => 54, 'max' => 56]
        ];

        $dataBase = [
            'act' => true
        ];

        DB::table('size')->truncate();

        $i = 0;
        foreach ($listAge as $row) {
            $row['sort'] = $i++;
            Model\Size::create(array_merge($dataBase, $row));
        }
    }
}
