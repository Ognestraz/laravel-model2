<?php

namespace Ognestraz\Tests\Unit;

use Tests\TestCase;
use Model\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\DB;


class SiteTest extends TestCase
{
    static protected $modelClass = Site::class;

    use RefreshDatabase;
    use Traits\Treeable;
    use Traits\Menuable;
//    use Traits\Path;

//    public function setUp()
//    {
//        parent::setUp();

//        $factory = new Factory(\Faker\Factory::create());
//        $factory->define(Site::class, function (Faker $faker) {
//            return [
//                'name' => $faker->name,
//                'path' => $faker->slug,
//                'order' => 0,
//                'parent_id' => 0,
//                'act' => true
//            ];
//        });        
//
//        for ($i = 0; $i < 10; $i++) {
//            $factory->of(Site::class)->make([
//                'name' => 'Test' . $i,
//                'path' => 'Test' . $i
//            ])->save();
//        }
//    }

}
