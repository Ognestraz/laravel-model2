<?php

namespace Ognestraz\Tests\Unit\Traits;

use Illuminate\Support\Facades\DB;
use Model\Menu;
use Model\Site;

trait Menuable
{
    /**
     *
     * @return void
     */
    public function testMenuableFirstRoot()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Test1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0]
        ], self::getTree(null, Site::class));

        $this->assertEquals([], self::getTree(null, Menu::class));

        $this->assertEquals([], Site::find(1)->menu->toArray());
        $this->assertEquals(null, Menu::find(1));
        
        $this->assertEquals(7, count(DB::getQueryLog()));
    }
    
    /**
     *
     * @return void
     */
    public function testMenuableToOneMenu()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Menu1', 'path' => '']
        ], Menu::class);        
        
        self::buildTree([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addToMenu(1);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'childs' => [
                    ['id' => 2, 'path' => 'Site1', 'order' => 0]
                ]
            ]
        ], self::getTree(null, Menu::class));

        $this->assertEquals([2], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);
        $this->assertEquals(1, Menu::find(2)->menuable->id);

        $this->assertEquals(20, count(DB::getQueryLog()));
    }
    
    
    /**
     *
     * @return void
     */
    public function testMenuableOneSiteTwoMenu()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
        ], Menu::class);        
        
        self::buildTree([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addToMenu(1);
        Site::find(1)->addToMenu(2);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'childs' => [
                    ['id' => 3, 'path' => 'Site1', 'order' => 0]
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1,
                'childs' => [
                    ['id' => 4, 'path' => 'Site1', 'order' => 0]
                ]
            ]            
        ], self::getTree(null, Menu::class));

        $this->assertEquals([3, 4], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);   
        $this->assertEquals(null, Menu::find(2)->menuable);   
        $this->assertEquals(1, Menu::find(3)->menuable->id);   
        $this->assertEquals(1, Menu::find(4)->menuable->id);        

        $this->assertEquals(34, count(DB::getQueryLog()));
    }
    
    /**
     *
     * @return void
     */
    public function testMenuableOneSiteTwoMenuDiffLevel()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
            ['name' => 'Menu3', 'path' => '', 'parent_id' => 1],
        ], Menu::class);        
        
        self::buildTree([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addToMenu(1);
        Site::find(1)->addToMenu(3);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'childs' => [
                    ['id' => 3, 'path' => '', 'order' => 0,
                        'childs' => [
                            ['id' => 5, 'path' => 'Site1', 'order' => 0]
                        ]
                    ],
                    ['id' => 4, 'path' => 'Site1', 'order' => 1]
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1]            
        ], self::getTree(null, Menu::class));

        $this->assertEquals([4, 5], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);
        $this->assertEquals(null, Menu::find(2)->menuable);
        $this->assertEquals(null, Menu::find(3)->menuable);
        $this->assertEquals(1, Menu::find(4)->menuable->id);
        $this->assertEquals(1, Menu::find(5)->menuable->id);
        
        $this->assertEquals(43, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableSomeSiteMenuDiffLevel()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Menu1', 'path' => '']
        ], Menu::class);        
        
        self::buildTree([
            ['name' => 'Site1'],
            ['name' => 'Site2'],
            ['name' => 'Site3', 'parent_id' => 1],
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0,
                'childs' => [
                    ['id' => 3, 'path' => 'Site1/Site3', 'order' => 0]
                ]
            ],
            ['id' => 2, 'path' => 'Site2', 'order' => 1],
        ], self::getTree(null, Site::class));

        Site::find(1)->addToMenu(1);
        Site::find(3)->addToMenu(2);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'childs' => [
                    ['id' => 2, 'path' => 'Site1', 'order' => 0,
                        'childs' => [
                            ['id' => 3, 'path' => 'Site1/Site3', 'order' => 0]
                        ]
                    ]
                ]
            ]          
        ], self::getTree(null, Menu::class));

        $this->assertEquals([2], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals([3], Site::find(3)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);
        $this->assertEquals(1, Menu::find(2)->menuable->id);
        $this->assertEquals(3, Menu::find(3)->menuable->id);

        $this->assertEquals(42, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableFull()
    {
        DB::enableQueryLog();
        self::buildTree([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
            ['name' => 'Menu3', 'path' => ''],
        ], Menu::class);        
        
        self::buildTree([
            ['name' => 'Site1'],
            ['name' => 'Site2'],
            ['name' => 'Site3'],
            ['name' => 'Site4', 'parent_id' => 1],
            ['name' => 'Site5', 'parent_id' => 1],
            ['name' => 'Site6', 'parent_id' => 3],
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0,
                'childs' => [
                    ['id' => 4, 'path' => 'Site1/Site4', 'order' => 0],
                    ['id' => 5, 'path' => 'Site1/Site5', 'order' => 1],
                ]
            ],
            ['id' => 2, 'path' => 'Site2', 'order' => 1],
            ['id' => 3, 'path' => 'Site3', 'order' => 2,
                'childs' => [
                    ['id' => 6, 'path' => 'Site3/Site6', 'order' => 0]
                ]
            ],
        ], self::getTree(null, Site::class));

        Site::find(1)->addToMenu(1);
        Site::find(4)->addToMenu(4);
        Site::find(5)->addToMenu(4);
        Site::find(2)->addToMenu(1);
        Site::find(3)->addToMenu(1);
        Site::find(6)->addToMenu(8);

        Site::find(4)->addToMenu(2);
        Site::find(5)->addToMenu(2);
        Site::find(6)->addToMenu(2);
        
        Site::find(6)->addToMenu(3);
        Site::find(2)->addToMenu(13);
        Site::find(5)->addToMenu(13);
        Site::find(3)->addToMenu(15);
        Site::find(2)->addToMenu(3);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'childs' => [
                    ['id' => 4, 'path' => 'Site1', 'order' => 0,
                        'childs' => [
                            ['id' => 5, 'path' => 'Site1/Site4', 'order' => 0],
                            ['id' => 6, 'path' => 'Site1/Site5', 'order' => 1],
                        ]
                    ],
                    ['id' => 7, 'path' => 'Site2', 'order' => 1],
                    ['id' => 8, 'path' => 'Site3', 'order' => 2,
                        'childs' => [
                            ['id' => 9, 'path' => 'Site3/Site6', 'order' => 0]
                        ]
                    ],
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1,
                'childs' => [
                    ['id' => 10, 'path' => 'Site1/Site4', 'order' => 0],
                    ['id' => 11, 'path' => 'Site1/Site5', 'order' => 1],
                    ['id' => 12, 'path' => 'Site3/Site6', 'order' => 2],
                ]
            ],
            ['id' => 3, 'path' => '', 'order' => 2,
                'childs' => [
                    ['id' => 13, 'path' => 'Site3/Site6', 'order' => 0,
                        'childs' => [
                            ['id' => 14, 'path' => 'Site2', 'order' => 0],
                            ['id' => 15, 'path' => 'Site1/Site5', 'order' => 1,
                                'childs' => [
                                    ['id' => 16, 'path' => 'Site3', 'order' => 0]
                                ]
                            ],
                        ]
                    ],
                    ['id' => 17, 'path' => 'Site2', 'order' => 1],
                ]
            ],
        ], self::getTree(null, Menu::class));

        $this->assertEquals([4], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals([7, 14, 17], Site::find(2)->menu->pluck('id')->toArray());
        $this->assertEquals([8, 16], Site::find(3)->menu->pluck('id')->toArray());
        $this->assertEquals([5, 10], Site::find(4)->menu->pluck('id')->toArray());
        $this->assertEquals([6, 11, 15], Site::find(5)->menu->pluck('id')->toArray());
        $this->assertEquals([9, 12, 13], Site::find(6)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);
        $this->assertEquals(null, Menu::find(2)->menuable);
        $this->assertEquals(null, Menu::find(3)->menuable);
        $this->assertEquals(1, Menu::find(4)->menuable->id);
        
        $this->assertEquals(200, count(DB::getQueryLog()));
    }    
}
