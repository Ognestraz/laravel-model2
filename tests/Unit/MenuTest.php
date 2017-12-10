<?php

namespace Ognestraz\Tests\Unit;

use Tests\TestCase;
use Model\Menu;
use Model\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\DB;

class MenuTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp()
    {
        parent::setUp();

        $factory = new Factory(\Faker\Factory::create());
        $factory->define(Menu::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
                'path' => $faker->slug,
                'order' => 0,
                'parent_id' => 0,
                'act' => true
            ];
        });        

        for ($i = 0; $i < 10; $i++) {
            $factory->of(Menu::class)->make([
                'name' => 'Test' . $i,
                'path' => 'Test' . $i
            ])->save();
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasic()
    {
        $this->assertEquals('Test0', Menu::find(1)->name);
        $this->assertEquals('Test0', Menu::find(1)->path);
        $this->assertEquals(0, Menu::find(1)->parent_id);
        $this->assertEquals(9, Menu::find(10)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testMenuSave()
    {
        $newSite = new Site();
        $newSite->name = 'New1';
        $newSite->save();

        $newSite2 = new Site();
        $newSite2->name = 'New2';
        $newSite2->save();        

        $menu = Menu::find(1);
        $newSite2->menu()->save($menu);

        $this->assertEquals(null, Menu::find(2)->menuable);

        $this->assertEquals(2, $menu->menuable->id);
        $this->assertEquals(0, $menu->menuable->parent_id);
        $this->assertEquals('New2', $menu->menuable->name);
        $this->assertEquals('New2', $menu->menuable->path);
        $this->assertEquals('Test0', Menu::find(1)->name);
        $this->assertEquals('Test0', Menu::find(1)->path);
        $this->assertEquals(0, Menu::find(1)->parent_id);

        $menu = Menu::find(1);
        $newSite->menu()->save($menu);

        $this->assertEquals(1, $menu->menuable->id);
        $this->assertEquals(0, $menu->menuable->parent_id);
        $this->assertEquals('New1', $menu->menuable->name);
        $this->assertEquals('New1', $menu->menuable->path);
        $this->assertEquals('Test0', Menu::find(1)->name);
        $this->assertEquals('Test0', Menu::find(1)->path);
        $this->assertEquals(0, Menu::find(1)->parent_id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testMenuCreate()
    {
        $newSite = new Site();
        $newSite->name = 'New1';
        $newSite->save();

        $newSite->menu()->create([
            'name' => $newSite->name,
            'path' => $newSite->path
        ]);

        $this->assertEquals('New1', Menu::find(11)->name);
        $this->assertEquals('New1', Menu::find(11)->path);
        $this->assertEquals('New1', Menu::find(11)->menuable->name);
        $this->assertEquals('New1', Menu::find(11)->menuable->path);
    }

}
