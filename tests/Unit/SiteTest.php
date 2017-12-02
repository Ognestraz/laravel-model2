<?php

namespace Ognestraz\Tests\Unit;

use Ognestraz\Tests\TestCase;
use Model\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;


class SiteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $factory = new Factory(\Faker\Factory::create());
        
        $factory->define(Site::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
                'path' => $faker->name,
                'order' => 0,
                'act' => 1,
            ];
        });
        
        //$factory = app(EloquentFactory::class);

        $site = $factory->of(Site::class)->make();
        
        print_r($site->toArray());
        
//        $site = new Site();
//        $site->path = $faker->name;
//        $site->name = $faker->name;
//        $site->save();

    //    $this->assertEquals('test', Site::find(1)->name);
    }
}
