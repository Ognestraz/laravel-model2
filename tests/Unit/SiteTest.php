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
    use RefreshDatabase;
    
    public function setUp()
    {
        parent::setUp();

        $factory = new Factory(\Faker\Factory::create());
        $factory->define(Site::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
                'path' => $faker->slug,
                'order' => 0,
                'parent_id' => 0,
                'act' => true
            ];
        });        

        for ($i = 0; $i < 10; $i++) {
            $factory->of(Site::class)->make([
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
        $this->assertEquals('Test0', Site::find(1)->name);
        $this->assertEquals('Test0', Site::find(1)->path);
        $this->assertEquals(0, Site::find(1)->parent_id);
        $this->assertEquals(9, Site::find(10)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSortable()
    {
        $this->assertEquals(10, Site::all()->count());

        foreach (Site::all() as $model) {
            $this->assertEquals($model->order, $model->id - 1);
        }
    }
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSortableNormalize()
    {
        $deletedId = 5;
        Site::find($deletedId)->delete();
        
        foreach (Site::all() as $model) {
            if ($model->id > $deletedId) {
                $this->assertEquals($model->order, $model->id - 2);
            } else {
                $this->assertEquals($model->order, $model->id - 1);
            }
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPath()
    {
        $newSite = new Site();
        $newSite->name = 'New1';
        $newSite->save();
        $this->assertEquals('New1', Site::find(11)->name);
        $this->assertEquals('New1', Site::find(11)->path);
        
        $newSite = new Site();
        $newSite->name = 'New2';
        $newSite->path = 'Path2';
        $newSite->save();
        $this->assertEquals(12, Site::findPath('Path2')->first()->id);
        $this->assertEquals('New2', Site::find(12)->name);
        $this->assertEquals('Path2', Site::find(12)->path);

        $newSite = new Site();
        $newSite->name = 'New3';
        $newSite->path = '';
        $newSite->save();
        $this->assertEquals(13, Site::findPath('')->first()->id);
        $this->assertEquals('New3', Site::find(13)->name);
        $this->assertEquals('', Site::find(13)->path);
        
        $newSite = new Site();
        $newSite->name = 'New4';
        $newSite->path = '/';
        $newSite->save();
        $this->assertEquals(14, Site::findPath('/')->first()->id);
        $this->assertEquals('New4', Site::find(14)->name);
        $this->assertEquals('/', Site::find(14)->path);

        $site = Site::findPath('/')->first();
        $site->path = 'test';
        $site->save();

        $newSite = new Site();
        $newSite->name = 'New5';
        $newSite->parent_id = 0;
        $newSite->path = '/';
        $newSite->save();
        $this->assertEquals(15, Site::findPath('/')->first()->id);
        $this->assertEquals('New5', Site::find(15)->name);
        $this->assertEquals('/', Site::find(15)->path);

        $newSite = new Site();
        $newSite->name = 'New6';
        $newSite->parent_id = 15;
        $newSite->save();
        $this->assertEquals('New6', Site::find(16)->name);
        $this->assertEquals('/New6', Site::find(16)->path);

        Site::find(1)->setParent(13);
        Site::find(2)->setParent(13);
        Site::find(3)->setParent(13);
        $this->assertEquals('Test0', Site::find(1)->path);
        $this->assertEquals('Test1', Site::find(2)->path);
        $this->assertEquals('Test2', Site::find(3)->path);
        
        Site::find(4)->setParent(15);
        Site::find(5)->setParent(15);
        Site::find(6)->setParent(15);
        $this->assertEquals('/Test3', Site::find(4)->path);
        $this->assertEquals('/Test4', Site::find(5)->path);
        $this->assertEquals('/Test5', Site::find(6)->path);        

        Site::find(4)->setParent(13);
        $this->assertEquals('Test3', Site::find(4)->path);
        
        Site::find(5)->setParent(6);
        $this->assertEquals('/Test5/Test4', Site::find(5)->path);
        
        Site::find(5)->setParent(13);
        $this->assertEquals('Test4', Site::find(5)->path);        

        Site::find(2)->setParent(3);
        $this->assertEquals('Test2/Test1', Site::find(2)->path);
        
        Site::find(2)->setParent(15);
        $this->assertEquals('/Test1', Site::find(2)->path);

        $this->assertEquals(null, Site::findPath('not-found')->first());
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableMoveBefore()
    {
        Site::find(2)->setParent(1);
        Site::find(3)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(5)->setParent(1);
        Site::find(6)->setParent(1);
        Site::find(7)->setParent(5);
        Site::find(8)->setParent(5);
        Site::find(9)->setParent(5);
        Site::find(10)->setParent(9);

        $this->assertEquals(0, Site::find(2)->order);
        $this->assertEquals(1, Site::find(3)->order);
        $this->assertEquals(2, Site::find(4)->order);
        $this->assertEquals(3, Site::find(5)->order);
        $this->assertEquals(4, Site::find(6)->order);
        $this->assertEquals(0, Site::find(7)->order);
        $this->assertEquals(1, Site::find(8)->order);
        $this->assertEquals(2, Site::find(9)->order);
        $this->assertEquals(0, Site::find(10)->order);

        Site::find(4)->moveBefore(2);
        $this->assertEquals(0, Site::find(4)->order);
        $this->assertEquals(1, Site::find(2)->order);
        $this->assertEquals(2, Site::find(3)->order);
        $this->assertEquals(3, Site::find(5)->order);
        $this->assertEquals(4, Site::find(6)->order);

        Site::find(3)->moveBefore(2);
        $this->assertEquals(0, Site::find(4)->order);
        $this->assertEquals(1, Site::find(3)->order);
        $this->assertEquals(2, Site::find(2)->order);
        $this->assertEquals(3, Site::find(5)->order);
        $this->assertEquals(4, Site::find(6)->order);
        
        Site::find(4)->moveBefore(2);
        $this->assertEquals(0, Site::find(3)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(2)->order);
        $this->assertEquals(3, Site::find(5)->order);
        $this->assertEquals(4, Site::find(6)->order);
        
        Site::find(4)->moveBefore(6);
        $this->assertEquals(0, Site::find(3)->order);
        $this->assertEquals(1, Site::find(2)->order);
        $this->assertEquals(2, Site::find(5)->order);
        $this->assertEquals(3, Site::find(4)->order);
        $this->assertEquals(4, Site::find(6)->order);

        $this->assertEquals(5, Site::find(1)->childs()->count());
        $this->assertEquals(3, Site::find(5)->childs()->count());
        $this->assertEquals('Test0/Test3', Site::find(4)->path);
        Site::find(4)->moveBefore(8);
        $this->assertEquals('Test0/Test4/Test3', Site::find(4)->path);
        $this->assertEquals(4, Site::find(1)->childs()->count());
        $this->assertEquals(4, Site::find(5)->childs()->count());
        $this->assertEquals(0, Site::find(3)->order);
        $this->assertEquals(1, Site::find(2)->order);
        $this->assertEquals(2, Site::find(5)->order);
        $this->assertEquals(3, Site::find(6)->order);

        $this->assertEquals(0, Site::find(7)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(8)->order);
        $this->assertEquals(3, Site::find(9)->order);
        
        $this->assertEquals(1, Site::find(9)->childs()->count());
        $this->assertEquals('Test0/Test4/Test7', Site::find(8)->path);
        Site::find(8)->moveBefore(10);
        $this->assertEquals('Test0/Test4/Test8/Test7', Site::find(8)->path);
        $this->assertEquals(3, Site::find(5)->childs()->count());
        $this->assertEquals(2, Site::find(9)->childs()->count());
        $this->assertEquals(0, Site::find(8)->order);
        $this->assertEquals(1, Site::find(10)->order);

        $this->assertEquals(0, Site::find(7)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(9)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test9', Site::find(10)->path);
        Site::find(10)->moveBefore(8);
        $this->assertEquals('Test0/Test4/Test8/Test9', Site::find(10)->path);
        $this->assertEquals(2, Site::find(9)->childs()->count());
        $this->assertEquals(0, Site::find(10)->order);
        $this->assertEquals(1, Site::find(8)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test9', Site::find(10)->path);
        Site::find(10)->moveBefore(7);
        $this->assertEquals('Test0/Test4/Test9', Site::find(10)->path);
        $this->assertEquals(1, Site::find(9)->childs()->count());
        $this->assertEquals(4, Site::find(5)->childs()->count());
        $this->assertEquals(0, Site::find(8)->order);

        $this->assertEquals(0, Site::find(10)->order);
        $this->assertEquals(1, Site::find(7)->order);
        $this->assertEquals(2, Site::find(4)->order);
        $this->assertEquals(3, Site::find(9)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test7', Site::find(8)->path);
        Site::find(8)->moveBefore(4);
        $this->assertEquals('Test0/Test4/Test7', Site::find(8)->path);
        $this->assertEquals(0, Site::find(9)->childs()->count());
        $this->assertEquals(5, Site::find(5)->childs()->count());
        $this->assertEquals(0, Site::find(10)->order);
        $this->assertEquals(1, Site::find(7)->order);
        $this->assertEquals(2, Site::find(8)->order);
        $this->assertEquals(3, Site::find(4)->order);
        $this->assertEquals(4, Site::find(9)->order);
        
        $this->assertEquals(1, (new Site())->childs()->count());
        Site::find(8)->moveBefore(1);
        $this->assertEquals('Test7', Site::find(8)->path);
        $this->assertEquals(2, (new Site())->childs()->count());
        $this->assertEquals(4, Site::find(5)->childs()->count());        
        $this->assertEquals(0, Site::find(10)->order);
        $this->assertEquals(1, Site::find(7)->order);
        $this->assertEquals(2, Site::find(4)->order);
        $this->assertEquals(3, Site::find(9)->order);

        $this->assertEquals(0, Site::find(8)->order);
        $this->assertEquals(1, Site::find(1)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableMoveAfter()
    {
        Site::find(2)->setParent(1);
        Site::find(3)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(5)->setParent(1);
        Site::find(6)->setParent(1);
        Site::find(7)->setParent(5);
        Site::find(8)->setParent(5);
        Site::find(9)->setParent(5);
        Site::find(10)->setParent(9);

        Site::find(4)->moveAfter(2);
        $this->assertEquals(0, Site::find(2)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(3)->order);
        $this->assertEquals(3, Site::find(5)->order);
        $this->assertEquals(4, Site::find(6)->order);

        Site::find(3)->moveAfter(5);
        $this->assertEquals(0, Site::find(2)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(5)->order);
        $this->assertEquals(3, Site::find(3)->order);
        $this->assertEquals(4, Site::find(6)->order);        

        Site::find(2)->moveAfter(6);
        $this->assertEquals(0, Site::find(4)->order);
        $this->assertEquals(1, Site::find(5)->order);
        $this->assertEquals(2, Site::find(3)->order);        
        $this->assertEquals(3, Site::find(6)->order);        
        $this->assertEquals(4, Site::find(2)->order);
        
        Site::find(2)->moveAfter(4);
        $this->assertEquals(0, Site::find(4)->order);
        $this->assertEquals(1, Site::find(2)->order); 
        $this->assertEquals(2, Site::find(5)->order);
        $this->assertEquals(3, Site::find(3)->order);        
        $this->assertEquals(4, Site::find(6)->order);
        
        $this->assertEquals(5, Site::find(1)->childs()->count());
        $this->assertEquals(3, Site::find(5)->childs()->count());
        $this->assertEquals('Test0/Test3', Site::find(4)->path);
        Site::find(4)->moveAfter(7);
        $this->assertEquals('Test0/Test4/Test3', Site::find(4)->path);
        $this->assertEquals(4, Site::find(1)->childs()->count());
        $this->assertEquals(4, Site::find(5)->childs()->count());
        
        $this->assertEquals(0, Site::find(2)->order); 
        $this->assertEquals(1, Site::find(5)->order); 
        $this->assertEquals(2, Site::find(3)->order);        
        $this->assertEquals(3, Site::find(6)->order);        
 
        $this->assertEquals(0, Site::find(7)->order);
        $this->assertEquals(1, Site::find(4)->order);
        $this->assertEquals(2, Site::find(8)->order);        
        $this->assertEquals(3, Site::find(9)->order);

        $this->assertEquals('Test0/Test2', Site::find(3)->path);
        Site::find(3)->moveAfter(9);
        $this->assertEquals('Test0/Test4/Test2', Site::find(3)->path);
        $this->assertEquals(3, Site::find(1)->childs()->count());
        $this->assertEquals(5, Site::find(5)->childs()->count());
        $this->assertEquals(0, Site::find(2)->order);
        $this->assertEquals(1, Site::find(5)->order);
        $this->assertEquals(2, Site::find(6)->order);  

        $this->assertEquals(0, Site::find(7)->order);
        $this->assertEquals(1, Site::find(4)->order); 
        $this->assertEquals(2, Site::find(8)->order);        
        $this->assertEquals(3, Site::find(9)->order);
        $this->assertEquals(4, Site::find(3)->order);

        $this->assertEquals(1, Site::find(9)->childs()->count());
        $this->assertEquals('Test0/Test5', Site::find(6)->path);
        Site::find(6)->moveAfter(10);
        $this->assertEquals('Test0/Test4/Test8/Test5', Site::find(6)->path);
        $this->assertEquals(2, Site::find(1)->childs()->count());
        $this->assertEquals(5, Site::find(5)->childs()->count());
        $this->assertEquals(0, Site::find(2)->order); 
        $this->assertEquals(1, Site::find(5)->order);

        $this->assertEquals(0, Site::find(10)->order);
        $this->assertEquals(1, Site::find(6)->order);

        $this->assertEquals('Test0/Test1', Site::find(2)->path);
        Site::find(2)->moveAfter(1);
        $this->assertEquals('Test1', Site::find(2)->path);
        $this->assertEquals(1, Site::find(1)->childs()->count());
        $this->assertEquals(2, (new Site())->childs()->count());
        $this->assertEquals(0, Site::find(5)->order);

        $this->assertEquals(0, Site::find(1)->order);
        $this->assertEquals(1, Site::find(2)->order);

        $this->assertEquals('Test0/Test4', Site::find(5)->path);
        Site::find(5)->moveAfter(1);
        $this->assertEquals('Test4', Site::find(5)->path);
        $this->assertEquals(0, Site::find(1)->childs()->count());
        $this->assertEquals(3, (new Site())->childs()->count());

        $this->assertEquals(0, Site::find(1)->order);
        $this->assertEquals(1, Site::find(5)->order);
        $this->assertEquals(2, Site::find(2)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSetParentOneLevel()
    {
        Site::find(2)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(3)->setParent(1);

        $this->assertEquals(1, Site::find(2)->getParent()->id);
        $this->assertEquals(1, Site::find(3)->getParent()->id);
        $this->assertEquals(1, Site::find(4)->getParent()->id);

        $this->assertEquals(3, Site::find(1)->childs()->count());
        
        $childs = Site::find(1)->childs()->orderBy('order')->get();
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(4, $childs[1]->id);
        $this->assertEquals(3, $childs[2]->id);

        Site::find(4)->delete();
        $this->assertEquals(null, Site::find(4));
        
        $this->assertEquals(2, Site::find(1)->childs()->count());
        
        $childs = Site::find(1)->childs()->orderBy('order')->get();
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(3, $childs[1]->id);
        
        Site::find(1)->delete();
        $this->assertEquals(null, Site::find(1));
        $this->assertEquals(null, Site::find(2));
        $this->assertEquals(null, Site::find(3));
        $this->assertEquals(null, Site::find(4));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSetParentMultiLevel()
    {
        Site::find(2)->setParent(1);
        Site::find(3)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(5)->setParent(3);
        Site::find(6)->setParent(3);
        Site::find(7)->setParent(5);
        Site::find(8)->setParent(5);
        Site::find(9)->setParent(8);
        Site::find(10)->setParent(9);

        $this->assertEquals(3, Site::find(1)->childs()->count());
        $this->assertEquals(2, Site::find(5)->childs()->count());
        $this->assertEquals(1, Site::find(8)->childs()->count());
        $this->assertEquals(1, Site::find(9)->childs()->count());

        Site::find(5)->delete();

        $this->assertNotEquals(null, Site::find(1));
        $this->assertNotEquals(null, Site::find(2));
        $this->assertNotEquals(null, Site::find(3));
        $this->assertNotEquals(null, Site::find(4));
        $this->assertEquals(null, Site::find(5));
        $this->assertNotEquals(null, Site::find(6));
        $this->assertEquals(null, Site::find(7));
        $this->assertEquals(null, Site::find(8));
        $this->assertEquals(null, Site::find(9));
        $this->assertEquals(null, Site::find(10));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableBreadcrumbs()
    {
        Site::find(2)->setParent(1);
        Site::find(3)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(5)->setParent(3);
        Site::find(6)->setParent(3);
        Site::find(7)->setParent(5);
        Site::find(8)->setParent(5);
        Site::find(9)->setParent(8);
        Site::find(10)->setParent(9);

        $way = Site::find(1)->getBreadcrumbs();
        $this->assertEquals(1, $way->count());
        $this->assertEquals(1, $way[0]->id);
        $this->assertEquals('Test0', $way[0]->path);

        $way = Site::find(2)->getBreadcrumbs();
        $this->assertEquals(2, $way->count());
        $this->assertEquals(1, $way[0]->id);
        $this->assertEquals('Test0', $way[0]->path);
        $this->assertEquals(2, $way[1]->id);        
        $this->assertEquals('Test0/Test1', $way[1]->path);

        $way = Site::find(10)->getBreadcrumbs();
        $this->assertEquals(6, $way->count());
        $this->assertEquals(1, $way[0]->id);
        $this->assertEquals('Test0', $way[0]->path);
        $this->assertEquals(3, $way[1]->id);
        $this->assertEquals('Test0/Test2', $way[1]->path);
        $this->assertEquals(5, $way[2]->id);
        $this->assertEquals('Test0/Test2/Test4', $way[2]->path);
        $this->assertEquals(8, $way[3]->id);
        $this->assertEquals('Test0/Test2/Test4/Test7', $way[3]->path);
        $this->assertEquals(9, $way[4]->id);
        $this->assertEquals('Test0/Test2/Test4/Test7/Test8', $way[4]->path);
        $this->assertEquals(10, $way[5]->id);
        $this->assertEquals('Test0/Test2/Test4/Test7/Test8/Test9', $way[5]->path);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableGetTree()
    {
        Site::find(2)->setParent(1);
        Site::find(3)->setParent(1);
        Site::find(4)->setParent(1);
        Site::find(5)->setParent(3);
        Site::find(6)->setParent(3);
        Site::find(7)->setParent(5);
        Site::find(8)->setParent(5);
        Site::find(9)->setParent(8);
        Site::find(10)->setParent(9);

        DB::enableQueryLog();
        $tree = Site::find(1)->getTree();
        
        $this->assertEquals(2, $tree[0]['model']->id);
        $this->assertEquals(3, $tree[1]['model']->id);
        $this->assertEquals(5, $tree[1]['childs'][0]['model']->id);
        $this->assertEquals(6, $tree[1]['childs'][1]['model']->id);
        $this->assertEquals(7, $tree[1]['childs'][0]['childs'][0]['model']->id);
        $this->assertEquals(8, $tree[1]['childs'][0]['childs'][1]['model']->id);
        $this->assertEquals(9, $tree[1]['childs'][0]['childs'][1]['childs'][0]['model']->id);
        $this->assertEquals(10, $tree[1]['childs'][0]['childs'][1]['childs'][0]['childs'][0]['model']->id);
        $this->assertEquals(4, $tree[2]['model']->id);

        $childs = Site::find(1)->getChilds();
        $this->assertEquals(3, $childs->count());
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(3, $childs[1]->id);
        $this->assertEquals(4, $childs[2]->id);

        $childs = Site::find(3)->getChilds();
        $this->assertEquals(2, $childs->count());
        $this->assertEquals(5, $childs[0]->id);
        $this->assertEquals(6, $childs[1]->id);

        $childs = Site::find(5)->getChilds();
        $this->assertEquals(2, $childs->count());
        $this->assertEquals(7, $childs[0]->id);
        $this->assertEquals(8, $childs[1]->id);

        $childs = Site::find(8)->getChilds();
        $this->assertEquals(1, $childs->count());
        $this->assertEquals(9, $childs[0]->id);

        $childs = Site::find(9)->getChilds();
        $this->assertEquals(1, $childs->count());
        $this->assertEquals(10, $childs[0]->id);

        $childs = Site::find(10)->getChilds();
        $this->assertEquals(0, $childs->count());

        $this->assertEquals(8, count(DB::getQueryLog()));
    }

}
