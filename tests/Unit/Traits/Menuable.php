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
        self::createItems([
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
        self::createItems([
            ['name' => 'Menu1', 'path' => '']
        ], Menu::class);        
        
        self::createItems([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addMenu(1);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Site1', 'order' => 0]
                ]
            ]
        ], self::getTree(null, Menu::class));

        $this->assertEquals([2], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);
        $this->assertEquals(1, Menu::find(2)->menuable->id);

        $this->assertEquals(19, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableOneSiteTwoMenu()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
        ], Menu::class);        
        
        self::createItems([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addMenu(1);
        Site::find(1)->addMenu(2);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Site1', 'order' => 0]
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1,
                'children' => [
                    ['id' => 4, 'path' => 'Site1', 'order' => 0]
                ]
            ]            
        ], self::getTree(null, Menu::class));

        $this->assertEquals([3, 4], Site::find(1)->menu->pluck('id')->toArray());
        $this->assertEquals(null, Menu::find(1)->menuable);   
        $this->assertEquals(null, Menu::find(2)->menuable);   
        $this->assertEquals(1, Menu::find(3)->menuable->id);   
        $this->assertEquals(1, Menu::find(4)->menuable->id);        

        $this->assertEquals(32, count(DB::getQueryLog()));
    }
    
    /**
     *
     * @return void
     */
    public function testMenuableOneSiteTwoMenuDiffLevel()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
            ['name' => 'Menu3', 'path' => '', 'parent_id' => 1],
        ], Menu::class);        
        
        self::createItems([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->addMenu(1);
        Site::find(1)->addMenu(3);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => '', 'order' => 0,
                        'children' => [
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
        
        $this->assertEquals(41, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableSomeSiteMenuDiffLevel()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Menu1', 'path' => '']
        ], Menu::class);        
        
        self::createItems([
            ['name' => 'Site1'],
            ['name' => 'Site2'],
            ['name' => 'Site3', 'parent_id' => 1],
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Site1/Site3', 'order' => 0]
                ]
            ],
            ['id' => 2, 'path' => 'Site2', 'order' => 1],
        ], self::getTree(null, Site::class));

        Site::find(1)->addMenu(1);
        Site::find(3)->addMenu(2);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Site1', 'order' => 0,
                        'children' => [
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

        $this->assertEquals(40, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableFull()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
            ['name' => 'Menu3', 'path' => ''],
        ], Menu::class);        
        
        self::createItems([
            ['name' => 'Site1'],
            ['name' => 'Site2'],
            ['name' => 'Site3'],
            ['name' => 'Site4', 'parent_id' => 1],
            ['name' => 'Site5', 'parent_id' => 1],
            ['name' => 'Site6', 'parent_id' => 3],
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0,
                'children' => [
                    ['id' => 4, 'path' => 'Site1/Site4', 'order' => 0],
                    ['id' => 5, 'path' => 'Site1/Site5', 'order' => 1],
                ]
            ],
            ['id' => 2, 'path' => 'Site2', 'order' => 1],
            ['id' => 3, 'path' => 'Site3', 'order' => 2,
                'children' => [
                    ['id' => 6, 'path' => 'Site3/Site6', 'order' => 0]
                ]
            ],
        ], self::getTree(null, Site::class));

        Site::find(1)->addMenu(1);
        Site::find(4)->addMenu(4);
        Site::find(5)->addMenu(4);
        Site::find(2)->addMenu(1);
        Site::find(3)->addMenu(1);
        Site::find(6)->addMenu(8);

        Site::find(4)->addMenu(2);
        Site::find(5)->addMenu(2);
        Site::find(6)->addMenu(2);
        
        Site::find(6)->addMenu(3);
        Site::find(2)->addMenu(13);
        Site::find(5)->addMenu(13);
        Site::find(3)->addMenu(15);
        Site::find(2)->addMenu(3);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 4, 'path' => 'Site1', 'order' => 0,
                        'children' => [
                            ['id' => 5, 'path' => 'Site1/Site4', 'order' => 0],
                            ['id' => 6, 'path' => 'Site1/Site5', 'order' => 1],
                        ]
                    ],
                    ['id' => 7, 'path' => 'Site2', 'order' => 1],
                    ['id' => 8, 'path' => 'Site3', 'order' => 2,
                        'children' => [
                            ['id' => 9, 'path' => 'Site3/Site6', 'order' => 0]
                        ]
                    ],
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1,
                'children' => [
                    ['id' => 10, 'path' => 'Site1/Site4', 'order' => 0],
                    ['id' => 11, 'path' => 'Site1/Site5', 'order' => 1],
                    ['id' => 12, 'path' => 'Site3/Site6', 'order' => 2],
                ]
            ],
            ['id' => 3, 'path' => '', 'order' => 2,
                'children' => [
                    ['id' => 13, 'path' => 'Site3/Site6', 'order' => 0,
                        'children' => [
                            ['id' => 14, 'path' => 'Site2', 'order' => 0],
                            ['id' => 15, 'path' => 'Site1/Site5', 'order' => 1,
                                'children' => [
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
        
        $this->assertEquals(186, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testMenuableOneSiteSyncMenu()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Menu1', 'path' => ''],
            ['name' => 'Menu2', 'path' => ''],
            ['name' => 'Menu3', 'path' => ''],
            ['name' => 'Menu4', 'path' => ''],
            ['name' => 'Menu5', 'path' => ''],
        ], Menu::class);

        self::createItems([
            ['name' => 'Site1']
        ], Site::class);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Site1', 'order' => 0]
        ], self::getTree(null, Site::class));

        Site::find(1)->syncMenu([1, 2, 3]);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 6, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 3, 'path' => '', 'order' => 2,
                'children' => [
                    ['id' => 8, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 4, 'path' => '', 'order' => 3],
            ['id' => 5, 'path' => '', 'order' => 4],
        ], self::getTree(null, Menu::class));

        Site::find(1)->syncMenu([1, 4, 5]);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0,
                'children' => [
                    ['id' => 6, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 2, 'path' => '', 'order' => 1],
            ['id' => 3, 'path' => '', 'order' => 2],
            ['id' => 4, 'path' => '', 'order' => 3,
                'children' => [
                    ['id' => 9, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 5, 'path' => '', 'order' => 4,
                'children' => [
                    ['id' => 10, 'path' => 'Site1', 'order' => 0],
                ]
            ],
        ], self::getTree(null, Menu::class));
        
        Site::find(1)->syncMenu([2, 3]);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0],
            ['id' => 2, 'path' => '', 'order' => 1,
                'children' => [
                    ['id' => 11, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 3, 'path' => '', 'order' => 2,
                'children' => [
                    ['id' => 12, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 4, 'path' => '', 'order' => 3],
            ['id' => 5, 'path' => '', 'order' => 4],
        ], self::getTree(null, Menu::class));
        
        Site::find(1)->syncMenu([2, 3]);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0],
            ['id' => 2, 'path' => '', 'order' => 1,
                'children' => [
                    ['id' => 11, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 3, 'path' => '', 'order' => 2,
                'children' => [
                    ['id' => 12, 'path' => 'Site1', 'order' => 0],
                ]
            ],
            ['id' => 4, 'path' => '', 'order' => 3],
            ['id' => 5, 'path' => '', 'order' => 4],
        ], self::getTree(null, Menu::class));
        
        Site::find(1)->syncMenu([]);
        $this->assertEquals([
            ['id' => 1, 'path' => '', 'order' => 0],
            ['id' => 2, 'path' => '', 'order' => 1],
            ['id' => 3, 'path' => '', 'order' => 2],
            ['id' => 4, 'path' => '', 'order' => 3],
            ['id' => 5, 'path' => '', 'order' => 4],
        ], self::getTree(null, Menu::class));

        $this->assertEquals(105, count(DB::getQueryLog()));
    }
}
