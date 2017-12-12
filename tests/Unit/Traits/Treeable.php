<?php

namespace Ognestraz\Tests\Unit\Traits;

use Illuminate\Support\Facades\DB;

trait Treeable
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableBasic()
    {
        $modelClass = static::$modelClass;
        $this->assertEquals('Test0', $modelClass::find(1)->name);
        $this->assertEquals('Test0', $modelClass::find(1)->path);
        $this->assertEquals(0, $modelClass::find(1)->parent_id);
        $this->assertEquals(9, $modelClass::find(10)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSortable()
    {
        $modelClass = static::$modelClass;
        $this->assertEquals(10, $modelClass::all()->count());

        foreach ($modelClass::all() as $model) {
            $this->assertEquals($model->order, $model->id - 1);
        }
    }
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSortableNormalize()
    {
        $modelClass = static::$modelClass;
        $deletedId = 5;
        $modelClass::find($deletedId)->delete();
        
        foreach ($modelClass::all() as $model) {
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
    public function testTreeablePath()
    {
        $modelClass = static::$modelClass;
        
        $newSite = new $modelClass();
        $newSite->name = 'New1';
        $newSite->save();
        $this->assertEquals('New1', $modelClass::find(11)->name);
        $this->assertEquals('New1', $modelClass::find(11)->path);
        
        $newSite = new $modelClass();
        $newSite->name = 'New2';
        $newSite->path = 'Path2';
        $newSite->save();
        $this->assertEquals(12, $modelClass::findPath('Path2')->first()->id);
        $this->assertEquals('New2', $modelClass::find(12)->name);
        $this->assertEquals('Path2', $modelClass::find(12)->path);

        $newSite = new $modelClass();
        $newSite->name = 'New3';
        $newSite->path = '';
        $newSite->save();
        $this->assertEquals(13, $modelClass::findPath('')->first()->id);
        $this->assertEquals('New3', $modelClass::find(13)->name);
        $this->assertEquals('', $modelClass::find(13)->path);
        
        $newSite = new $modelClass();
        $newSite->name = 'New4';
        $newSite->path = '/';
        $newSite->save();
        $this->assertEquals(14, $modelClass::findPath('/')->first()->id);
        $this->assertEquals('New4', $modelClass::find(14)->name);
        $this->assertEquals('/', $modelClass::find(14)->path);

        $site = $modelClass::findPath('/')->first();
        $site->path = 'test';
        $site->save();

        $newSite = new $modelClass();
        $newSite->name = 'New5';
        $newSite->parent_id = 0;
        $newSite->path = '/';
        $newSite->save();
        $this->assertEquals(15, $modelClass::findPath('/')->first()->id);
        $this->assertEquals('New5', $modelClass::find(15)->name);
        $this->assertEquals('/', $modelClass::find(15)->path);

        $newSite = new $modelClass();
        $newSite->name = 'New6';
        $newSite->parent_id = 15;
        $newSite->save();
        $this->assertEquals('New6', $modelClass::find(16)->name);
        $this->assertEquals('/New6', $modelClass::find(16)->path);

        $modelClass::find(1)->setParent(13);
        $modelClass::find(2)->setParent(13);
        $modelClass::find(3)->setParent(13);
        $this->assertEquals('Test0', $modelClass::find(1)->path);
        $this->assertEquals('Test1', $modelClass::find(2)->path);
        $this->assertEquals('Test2', $modelClass::find(3)->path);
        
        $modelClass::find(4)->setParent(15);
        $modelClass::find(5)->setParent(15);
        $modelClass::find(6)->setParent(15);
        $this->assertEquals('/Test3', $modelClass::find(4)->path);
        $this->assertEquals('/Test4', $modelClass::find(5)->path);
        $this->assertEquals('/Test5', $modelClass::find(6)->path);        

        $modelClass::find(4)->setParent(13);
        $this->assertEquals('Test3', $modelClass::find(4)->path);
        
        $modelClass::find(5)->setParent(6);
        $this->assertEquals('/Test5/Test4', $modelClass::find(5)->path);
        
        $modelClass::find(5)->setParent(13);
        $this->assertEquals('Test4', $modelClass::find(5)->path);        

        $modelClass::find(2)->setParent(3);
        $this->assertEquals('Test2/Test1', $modelClass::find(2)->path);
        
        $modelClass::find(2)->setParent(15);
        $this->assertEquals('/Test1', $modelClass::find(2)->path);

        $this->assertEquals(null, $modelClass::findPath('not-found')->first());
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableMoveBefore()
    {
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(5)->setParent(1);
        $modelClass::find(6)->setParent(1);
        $modelClass::find(7)->setParent(5);
        $modelClass::find(8)->setParent(5);
        $modelClass::find(9)->setParent(5);
        $modelClass::find(10)->setParent(9);

        $this->assertEquals(0, $modelClass::find(2)->order);
        $this->assertEquals(1, $modelClass::find(3)->order);
        $this->assertEquals(2, $modelClass::find(4)->order);
        $this->assertEquals(3, $modelClass::find(5)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);
        $this->assertEquals(0, $modelClass::find(7)->order);
        $this->assertEquals(1, $modelClass::find(8)->order);
        $this->assertEquals(2, $modelClass::find(9)->order);
        $this->assertEquals(0, $modelClass::find(10)->order);

        $modelClass::find(4)->moveBefore(2);
        $this->assertEquals(0, $modelClass::find(4)->order);
        $this->assertEquals(1, $modelClass::find(2)->order);
        $this->assertEquals(2, $modelClass::find(3)->order);
        $this->assertEquals(3, $modelClass::find(5)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);

        $modelClass::find(3)->moveBefore(2);
        $this->assertEquals(0, $modelClass::find(4)->order);
        $this->assertEquals(1, $modelClass::find(3)->order);
        $this->assertEquals(2, $modelClass::find(2)->order);
        $this->assertEquals(3, $modelClass::find(5)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);
        
        $modelClass::find(4)->moveBefore(2);
        $this->assertEquals(0, $modelClass::find(3)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(2)->order);
        $this->assertEquals(3, $modelClass::find(5)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);
        
        $modelClass::find(4)->moveBefore(6);
        $this->assertEquals(0, $modelClass::find(3)->order);
        $this->assertEquals(1, $modelClass::find(2)->order);
        $this->assertEquals(2, $modelClass::find(5)->order);
        $this->assertEquals(3, $modelClass::find(4)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);

        $this->assertEquals(5, $modelClass::find(1)->childs()->count());
        $this->assertEquals(3, $modelClass::find(5)->childs()->count());
        $this->assertEquals('Test0/Test3', $modelClass::find(4)->path);
        $modelClass::find(4)->moveBefore(8);
        $this->assertEquals('Test0/Test4/Test3', $modelClass::find(4)->path);
        $this->assertEquals(4, $modelClass::find(1)->childs()->count());
        $this->assertEquals(4, $modelClass::find(5)->childs()->count());
        $this->assertEquals(0, $modelClass::find(3)->order);
        $this->assertEquals(1, $modelClass::find(2)->order);
        $this->assertEquals(2, $modelClass::find(5)->order);
        $this->assertEquals(3, $modelClass::find(6)->order);

        $this->assertEquals(0, $modelClass::find(7)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(8)->order);
        $this->assertEquals(3, $modelClass::find(9)->order);
        
        $this->assertEquals(1, $modelClass::find(9)->childs()->count());
        $this->assertEquals('Test0/Test4/Test7', $modelClass::find(8)->path);
        $modelClass::find(8)->moveBefore(10);
        $this->assertEquals('Test0/Test4/Test8/Test7', $modelClass::find(8)->path);
        $this->assertEquals(3, $modelClass::find(5)->childs()->count());
        $this->assertEquals(2, $modelClass::find(9)->childs()->count());
        $this->assertEquals(0, $modelClass::find(8)->order);
        $this->assertEquals(1, $modelClass::find(10)->order);

        $this->assertEquals(0, $modelClass::find(7)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(9)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test9', $modelClass::find(10)->path);
        $modelClass::find(10)->moveBefore(8);
        $this->assertEquals('Test0/Test4/Test8/Test9', $modelClass::find(10)->path);
        $this->assertEquals(2, $modelClass::find(9)->childs()->count());
        $this->assertEquals(0, $modelClass::find(10)->order);
        $this->assertEquals(1, $modelClass::find(8)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test9', $modelClass::find(10)->path);
        $modelClass::find(10)->moveBefore(7);
        $this->assertEquals('Test0/Test4/Test9', $modelClass::find(10)->path);
        $this->assertEquals(1, $modelClass::find(9)->childs()->count());
        $this->assertEquals(4, $modelClass::find(5)->childs()->count());
        $this->assertEquals(0, $modelClass::find(8)->order);

        $this->assertEquals(0, $modelClass::find(10)->order);
        $this->assertEquals(1, $modelClass::find(7)->order);
        $this->assertEquals(2, $modelClass::find(4)->order);
        $this->assertEquals(3, $modelClass::find(9)->order);
        
        $this->assertEquals('Test0/Test4/Test8/Test7', $modelClass::find(8)->path);
        $modelClass::find(8)->moveBefore(4);
        $this->assertEquals('Test0/Test4/Test7', $modelClass::find(8)->path);
        $this->assertEquals(0, $modelClass::find(9)->childs()->count());
        $this->assertEquals(5, $modelClass::find(5)->childs()->count());
        $this->assertEquals(0, $modelClass::find(10)->order);
        $this->assertEquals(1, $modelClass::find(7)->order);
        $this->assertEquals(2, $modelClass::find(8)->order);
        $this->assertEquals(3, $modelClass::find(4)->order);
        $this->assertEquals(4, $modelClass::find(9)->order);
        
        $this->assertEquals(1, (new $modelClass())->childs()->count());
        $modelClass::find(8)->moveBefore(1);
        $this->assertEquals('Test7', $modelClass::find(8)->path);
        $this->assertEquals(2, (new $modelClass())->childs()->count());
        $this->assertEquals(4, $modelClass::find(5)->childs()->count());        
        $this->assertEquals(0, $modelClass::find(10)->order);
        $this->assertEquals(1, $modelClass::find(7)->order);
        $this->assertEquals(2, $modelClass::find(4)->order);
        $this->assertEquals(3, $modelClass::find(9)->order);

        $this->assertEquals(0, $modelClass::find(8)->order);
        $this->assertEquals(1, $modelClass::find(1)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableMoveAfter()
    {
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(5)->setParent(1);
        $modelClass::find(6)->setParent(1);
        $modelClass::find(7)->setParent(5);
        $modelClass::find(8)->setParent(5);
        $modelClass::find(9)->setParent(5);
        $modelClass::find(10)->setParent(9);

        $modelClass::find(4)->moveAfter(2);
        $this->assertEquals(0, $modelClass::find(2)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(3)->order);
        $this->assertEquals(3, $modelClass::find(5)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);

        $modelClass::find(3)->moveAfter(5);
        $this->assertEquals(0, $modelClass::find(2)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(5)->order);
        $this->assertEquals(3, $modelClass::find(3)->order);
        $this->assertEquals(4, $modelClass::find(6)->order);        

        $modelClass::find(2)->moveAfter(6);
        $this->assertEquals(0, $modelClass::find(4)->order);
        $this->assertEquals(1, $modelClass::find(5)->order);
        $this->assertEquals(2, $modelClass::find(3)->order);        
        $this->assertEquals(3, $modelClass::find(6)->order);        
        $this->assertEquals(4, $modelClass::find(2)->order);
        
        $modelClass::find(2)->moveAfter(4);
        $this->assertEquals(0, $modelClass::find(4)->order);
        $this->assertEquals(1, $modelClass::find(2)->order); 
        $this->assertEquals(2, $modelClass::find(5)->order);
        $this->assertEquals(3, $modelClass::find(3)->order);        
        $this->assertEquals(4, $modelClass::find(6)->order);
        
        $this->assertEquals(5, $modelClass::find(1)->childs()->count());
        $this->assertEquals(3, $modelClass::find(5)->childs()->count());
        $this->assertEquals('Test0/Test3', $modelClass::find(4)->path);
        $modelClass::find(4)->moveAfter(7);
        $this->assertEquals('Test0/Test4/Test3', $modelClass::find(4)->path);
        $this->assertEquals(4, $modelClass::find(1)->childs()->count());
        $this->assertEquals(4, $modelClass::find(5)->childs()->count());
        
        $this->assertEquals(0, $modelClass::find(2)->order); 
        $this->assertEquals(1, $modelClass::find(5)->order); 
        $this->assertEquals(2, $modelClass::find(3)->order);        
        $this->assertEquals(3, $modelClass::find(6)->order);        
 
        $this->assertEquals(0, $modelClass::find(7)->order);
        $this->assertEquals(1, $modelClass::find(4)->order);
        $this->assertEquals(2, $modelClass::find(8)->order);        
        $this->assertEquals(3, $modelClass::find(9)->order);

        $this->assertEquals('Test0/Test2', $modelClass::find(3)->path);
        $modelClass::find(3)->moveAfter(9);
        $this->assertEquals('Test0/Test4/Test2', $modelClass::find(3)->path);
        $this->assertEquals(3, $modelClass::find(1)->childs()->count());
        $this->assertEquals(5, $modelClass::find(5)->childs()->count());
        $this->assertEquals(0, $modelClass::find(2)->order);
        $this->assertEquals(1, $modelClass::find(5)->order);
        $this->assertEquals(2, $modelClass::find(6)->order);  

        $this->assertEquals(0, $modelClass::find(7)->order);
        $this->assertEquals(1, $modelClass::find(4)->order); 
        $this->assertEquals(2, $modelClass::find(8)->order);        
        $this->assertEquals(3, $modelClass::find(9)->order);
        $this->assertEquals(4, $modelClass::find(3)->order);

        $this->assertEquals(1, $modelClass::find(9)->childs()->count());
        $this->assertEquals('Test0/Test5', $modelClass::find(6)->path);
        $modelClass::find(6)->moveAfter(10);
        $this->assertEquals('Test0/Test4/Test8/Test5', $modelClass::find(6)->path);
        $this->assertEquals(2, $modelClass::find(1)->childs()->count());
        $this->assertEquals(5, $modelClass::find(5)->childs()->count());
        $this->assertEquals(0, $modelClass::find(2)->order); 
        $this->assertEquals(1, $modelClass::find(5)->order);

        $this->assertEquals(0, $modelClass::find(10)->order);
        $this->assertEquals(1, $modelClass::find(6)->order);

        $this->assertEquals('Test0/Test1', $modelClass::find(2)->path);
        $modelClass::find(2)->moveAfter(1);
        $this->assertEquals('Test1', $modelClass::find(2)->path);
        $this->assertEquals(1, $modelClass::find(1)->childs()->count());
        $this->assertEquals(2, (new $modelClass())->childs()->count());
        $this->assertEquals(0, $modelClass::find(5)->order);

        $this->assertEquals(0, $modelClass::find(1)->order);
        $this->assertEquals(1, $modelClass::find(2)->order);

        $this->assertEquals('Test0/Test4', $modelClass::find(5)->path);
        $modelClass::find(5)->moveAfter(1);
        $this->assertEquals('Test4', $modelClass::find(5)->path);
        $this->assertEquals(0, $modelClass::find(1)->childs()->count());
        $this->assertEquals(3, (new $modelClass())->childs()->count());

        $this->assertEquals(0, $modelClass::find(1)->order);
        $this->assertEquals(1, $modelClass::find(5)->order);
        $this->assertEquals(2, $modelClass::find(2)->order);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSetParentOneLevel()
    {
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(3)->setParent(1);

        $this->assertEquals(1, $modelClass::find(2)->getParent()->id);
        $this->assertEquals(1, $modelClass::find(3)->getParent()->id);
        $this->assertEquals(1, $modelClass::find(4)->getParent()->id);

        $this->assertEquals(3, $modelClass::find(1)->childs()->count());
        
        $childs = $modelClass::find(1)->childs()->orderBy('order')->get();
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(4, $childs[1]->id);
        $this->assertEquals(3, $childs[2]->id);

        $modelClass::find(4)->delete();
        $this->assertEquals(null, $modelClass::find(4));
        
        $this->assertEquals(2, $modelClass::find(1)->childs()->count());
        
        $childs = $modelClass::find(1)->childs()->orderBy('order')->get();
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(3, $childs[1]->id);
        
        $modelClass::find(1)->delete();
        $this->assertEquals(null, $modelClass::find(1));
        $this->assertEquals(null, $modelClass::find(2));
        $this->assertEquals(null, $modelClass::find(3));
        $this->assertEquals(null, $modelClass::find(4));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableSetParentMultiLevel()
    {
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(5)->setParent(3);
        $modelClass::find(6)->setParent(3);
        $modelClass::find(7)->setParent(5);
        $modelClass::find(8)->setParent(5);
        $modelClass::find(9)->setParent(8);
        $modelClass::find(10)->setParent(9);

        $this->assertEquals(3, $modelClass::find(1)->childs()->count());
        $this->assertEquals(2, $modelClass::find(5)->childs()->count());
        $this->assertEquals(1, $modelClass::find(8)->childs()->count());
        $this->assertEquals(1, $modelClass::find(9)->childs()->count());

        $modelClass::find(5)->delete();

        $this->assertNotEquals(null, $modelClass::find(1));
        $this->assertNotEquals(null, $modelClass::find(2));
        $this->assertNotEquals(null, $modelClass::find(3));
        $this->assertNotEquals(null, $modelClass::find(4));
        $this->assertEquals(null, $modelClass::find(5));
        $this->assertNotEquals(null, $modelClass::find(6));
        $this->assertEquals(null, $modelClass::find(7));
        $this->assertEquals(null, $modelClass::find(8));
        $this->assertEquals(null, $modelClass::find(9));
        $this->assertEquals(null, $modelClass::find(10));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableBreadcrumbs()
    {
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(5)->setParent(3);
        $modelClass::find(6)->setParent(3);
        $modelClass::find(7)->setParent(5);
        $modelClass::find(8)->setParent(5);
        $modelClass::find(9)->setParent(8);
        $modelClass::find(10)->setParent(9);

        $way = $modelClass::find(1)->getBreadcrumbs();
        $this->assertEquals(1, $way->count());
        $this->assertEquals(1, $way[0]->id);
        $this->assertEquals('Test0', $way[0]->path);

        $way = $modelClass::find(2)->getBreadcrumbs();
        $this->assertEquals(2, $way->count());
        $this->assertEquals(1, $way[0]->id);
        $this->assertEquals('Test0', $way[0]->path);
        $this->assertEquals(2, $way[1]->id);        
        $this->assertEquals('Test0/Test1', $way[1]->path);

        $way = $modelClass::find(10)->getBreadcrumbs();
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
        $modelClass = static::$modelClass;
        
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);
        $modelClass::find(4)->setParent(1);
        $modelClass::find(5)->setParent(3);
        $modelClass::find(6)->setParent(3);
        $modelClass::find(7)->setParent(5);
        $modelClass::find(8)->setParent(5);
        $modelClass::find(9)->setParent(8);
        $modelClass::find(10)->setParent(9);

        DB::enableQueryLog();
        $tree = $modelClass::find(1)->getTree();
        
        $this->assertEquals(2, $tree[0]['model']->id);
        $this->assertEquals(3, $tree[1]['model']->id);
        $this->assertEquals(5, $tree[1]['childs'][0]['model']->id);
        $this->assertEquals(6, $tree[1]['childs'][1]['model']->id);
        $this->assertEquals(7, $tree[1]['childs'][0]['childs'][0]['model']->id);
        $this->assertEquals(8, $tree[1]['childs'][0]['childs'][1]['model']->id);
        $this->assertEquals(9, $tree[1]['childs'][0]['childs'][1]['childs'][0]['model']->id);
        $this->assertEquals(10, $tree[1]['childs'][0]['childs'][1]['childs'][0]['childs'][0]['model']->id);
        $this->assertEquals(4, $tree[2]['model']->id);

        $childs = $modelClass::find(1)->getChilds();
        $this->assertEquals(3, $childs->count());
        $this->assertEquals(2, $childs[0]->id);
        $this->assertEquals(3, $childs[1]->id);
        $this->assertEquals(4, $childs[2]->id);

        $childs = $modelClass::find(3)->getChilds();
        $this->assertEquals(2, $childs->count());
        $this->assertEquals(5, $childs[0]->id);
        $this->assertEquals(6, $childs[1]->id);

        $childs = $modelClass::find(5)->getChilds();
        $this->assertEquals(2, $childs->count());
        $this->assertEquals(7, $childs[0]->id);
        $this->assertEquals(8, $childs[1]->id);

        $childs = $modelClass::find(8)->getChilds();
        $this->assertEquals(1, $childs->count());
        $this->assertEquals(9, $childs[0]->id);

        $childs = $modelClass::find(9)->getChilds();
        $this->assertEquals(1, $childs->count());
        $this->assertEquals(10, $childs[0]->id);

        $childs = $modelClass::find(10)->getChilds();
        $this->assertEquals(0, $childs->count());

        $this->assertEquals(8, count(DB::getQueryLog()));
    }

}
