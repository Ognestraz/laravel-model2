<?php

namespace Ognestraz\Tests\Unit\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait Treeable
{
    public static function getTree($id = null, $modelClass = null)
    {
        if (null === $modelClass) {
            $modelClass = static::$modelClass;
        }
        return (new $modelClass())->getTree($id);
    }

    /**
     *
     * @return void
     */
    public function testPathFirstRoot()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Test1']
        ]);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0]
        ], self::getTree());

        $this->assertEquals(3, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testPathTwoRoots()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
        ]);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1]
        ], self::getTree());        
    }

    /**
     *
     * @return void
     */
    public function testPathOneChildCreate()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2', 'parent_id' => 1],
        ]);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0]
                ]
            ]    
        ], self::getTree());        
    }

    /**
     *
     * @return void
     */
    public function testPathOneChildMoveSecondToFirst()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
        ]);
        
        $modelClass = static::$modelClass;
        $modelClass::find(2)->setParent(1);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0]
                ]
            ]
        ], self::getTree());
    }

    /**
     *
     * @return void
     */
    public function testPathOneChildMoveFirstToSecond()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
        ]);
        
        $modelClass = static::$modelClass;
        $modelClass::find(1)->setParent(2);
        
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0,
                'children' => [
                    ['id' => 1, 'path' => 'Test2/Test1', 'order' => 0]
                ]
            ]
        ], self::getTree());        
    }    
    
    /**
     *
     * @return void
     */
    public function testPathChildTwoLevel()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3']
        ]);
        
        $modelClass = static::$modelClass;
        $modelClass::find(2)->setParent(1);    
        $modelClass::find(3)->setParent(2);    
       
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0,
                        'children' => [
                            ['id' => 3, 'path' => 'Test1/Test2/Test3', 'order' => 0]
                        ]
                    ]
                ]
            ]
        ], self::getTree());
    }

    /**
     *
     * @return void
     */
    public function testPathTwoChild()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3']
        ]);
        
        $modelClass = static::$modelClass;
        $modelClass::find(2)->setParent(1);
        $modelClass::find(3)->setParent(1);    
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 1],
                ]
            ]
        ], self::getTree());
    }
    
    /**
     *
     * @return void
     */
    public function testPathMoveBitweenParents()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3']
        ]);
        
        $modelClass = static::$modelClass;
        $modelClass::find(2)->setParent(1);    
        $modelClass::find(3)->setParent(1);
        $modelClass::find(3)->setParent(2);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0,
                        'children' => [
                            ['id' => 3, 'path' => 'Test1/Test2/Test3', 'order' => 0]
                        ]
                    ]
                ]
            ]
        ], self::getTree());
    }

    /**
     *
     * @return void
     */
    public function testPathMoveOneChildUp()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3', 'parent_id' => 2]
        ]);

        $modelClass = static::$modelClass;
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1,
                'children' => [
                    ['id' => 3, 'path' => 'Test2/Test3', 'order' => 0]
                ]
            ]
        ], self::getTree());

        $modelClass::find(3)->moveAfter(1);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
        ], self::getTree());
    }

    /**
     *
     * @return void
     */
    public function testPathMoveLongChain()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5'],
            ['name' => 'Test6'],
            ['name' => 'Test7'],
        ]);

        $modelClass = static::$modelClass;
        $modelClass::find(2)->setParent(1);    
        $modelClass::find(3)->setParent(2);
        $modelClass::find(4)->setParent(3);
        $modelClass::find(5)->setParent(4);
        
        $modelClass::find(7)->setParent(6);

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0,
                        'children' => [
                            ['id' => 3, 'path' => 'Test1/Test2/Test3', 'order' => 0,
                                'children' => [
                                    ['id' => 4, 'path' => 'Test1/Test2/Test3/Test4', 'order' => 0,
                                        'children' => [
                                            ['id' => 5, 'path' => 'Test1/Test2/Test3/Test4/Test5', 'order' => 0]
                                        ]       
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            ['id' => 6, 'path' => 'Test6', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test6/Test7', 'order' => 0]
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->setParent(7);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 6, 'path' => 'Test6', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test6/Test7', 'order' => 0,
                        'children' => [
                            ['id' => 2, 'path' => 'Test6/Test7/Test2', 'order' => 0,
                                'children' => [
                                    ['id' => 3, 'path' => 'Test6/Test7/Test2/Test3', 'order' => 0,
                                        'children' => [
                                            ['id' => 4, 'path' => 'Test6/Test7/Test2/Test3/Test4', 'order' => 0,
                                                'children' => [
                                                    ['id' => 5, 'path' => 'Test6/Test7/Test2/Test3/Test4/Test5', 'order' => 0]
                                                ]                                                
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], self::getTree());        
    }    

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableOrderBasic()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5']
        ]);
        $modelClass = static::$modelClass;
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4],
        ], self::getTree());
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTreeableOrderDelete()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5']
        ]);
        $modelClass = static::$modelClass;        

        $modelClass::find(3)->delete();
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 4, 'path' => 'Test4', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3],
        ], self::getTree());
        
        $modelClass::find(1)->delete();
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2],
        ], self::getTree());
        
        $modelClass::find(5)->delete();
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
        ], self::getTree());
        
        $modelClass::find(2)->delete();
        $this->assertEquals([
            ['id' => 4, 'path' => 'Test4', 'order' => 0],
        ], self::getTree());
        
        $modelClass::find(4)->delete();
        $this->assertEquals([], self::getTree());           
    }

    /**
     * @return void
     */
    public function testTreeableMoveBeforeOneLevelUp()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5'],
        ]);
        $modelClass = static::$modelClass;

        $modelClass::find(4)->moveBefore(1);
        $this->assertEquals([
            ['id' => 4, 'path' => 'Test4', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4],
        ], self::getTree());
        
        $modelClass::find(3)->moveBefore(1);
        $this->assertEquals([
            ['id' => 4, 'path' => 'Test4', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 1, 'path' => 'Test1', 'order' => 2],
            ['id' => 2, 'path' => 'Test2', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4]
        ], self::getTree());
        
        $modelClass::find(5)->moveBefore(4);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 1, 'path' => 'Test1', 'order' => 3],
            ['id' => 2, 'path' => 'Test2', 'order' => 4],
        ], self::getTree());

        $modelClass::find(1)->moveBefore(3);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 1, 'path' => 'Test1', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 2, 'path' => 'Test2', 'order' => 4],
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveBeforeOneLevelDown()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5']
        ]);
        $modelClass = static::$modelClass;

        $modelClass::find(1)->moveBefore(5);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 4, 'path' => 'Test4', 'order' => 2],
            ['id' => 1, 'path' => 'Test1', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4]
        ], self::getTree());
        
        $modelClass::find(3)->moveBefore(5);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 1, 'path' => 'Test1', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4]
        ], self::getTree());
        
        $modelClass::find(2)->moveBefore(3);
        $this->assertEquals([
            ['id' => 4, 'path' => 'Test4', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4],
        ], self::getTree());

        $modelClass::find(1)->moveBefore(2);
        $this->assertEquals([
            ['id' => 4, 'path' => 'Test4', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4],
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveBeforeBetweenLevel() {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2', 'parent_id' => 1],
            ['name' => 'Test3', 'parent_id' => 1],
            ['name' => 'Test4', 'parent_id' => 1],
            ['name' => 'Test5'],
            ['name' => 'Test6', 'parent_id' => 5],
            ['name' => 'Test7', 'parent_id' => 5],
            ['name' => 'Test8', 'parent_id' => 5],
            ['name' => 'Test9', 'parent_id' => 5],
        ]);
        $modelClass = static::$modelClass;

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 1],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 2],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                ]
            ]
        ], self::getTree());

        $modelClass::find(6)->moveBefore(2);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 0],
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 1],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 2],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 3],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 1],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 2],
                ]
            ]
        ], self::getTree());

        $modelClass::find(9)->moveBefore(6);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 9, 'path' => 'Test1/Test9', 'order' => 0],
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 1],
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 2],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 3],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 4],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 1],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->moveBefore(8);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 9, 'path' => 'Test1/Test9', 'order' => 0],
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 1],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 2],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 3],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(6)->moveBefore(8);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 9, 'path' => 'Test1/Test9', 'order' => 0],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 1],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 2],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 1],
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 2],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 3],
                ]
            ]
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveBeforeDownLevel()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5'],
            ['name' => 'Test6', 'parent_id' => 5],
            ['name' => 'Test7', 'parent_id' => 5],
            ['name' => 'Test8', 'parent_id' => 5],
            ['name' => 'Test9', 'parent_id' => 5],
        ]);
        $modelClass = static::$modelClass;
        
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                ]
            ]
        ], self::getTree());

        $modelClass::find(1)->moveBefore(6);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 4, 'path' => 'Test4', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3,
                'children' => [
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 0],
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 1],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 3],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 4],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->moveBefore(9);
        $this->assertEquals([
            ['id' => 3, 'path' => 'Test3', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2,
                'children' => [
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 0],
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 1],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 3],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 4],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 5],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(4)->moveBefore(1);
        $this->assertEquals([
            ['id' => 3, 'path' => 'Test3', 'order' => 0],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 4, 'path' => 'Test5/Test4', 'order' => 0],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 1],
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 2],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 3],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 4],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 5],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 6],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(3)->moveBefore(8);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0,
                'children' => [
                    ['id' => 4, 'path' => 'Test5/Test4', 'order' => 0],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 1],
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 2],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 3],
                    ['id' => 3, 'path' => 'Test5/Test3', 'order' => 4],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 5],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 6],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 7],
                ]
            ]
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveBeforeUpLevel()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5', 'parent_id' => 4],
            ['name' => 'Test6', 'parent_id' => 4],
            ['name' => 'Test7', 'parent_id' => 4],
            ['name' => 'Test8', 'parent_id' => 4],
            ['name' => 'Test9', 'parent_id' => 4],
        ]);
        $modelClass = static::$modelClass;
        
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3,
                'children' => [
                    ['id' => 5, 'path' => 'Test4/Test5', 'order' => 0],
                    ['id' => 6, 'path' => 'Test4/Test6', 'order' => 1],
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 3],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 4],
                ]
            ]
        ], self::getTree());

        $modelClass::find(5)->moveBefore(1);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4,
                'children' => [
                    ['id' => 6, 'path' => 'Test4/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 3],
                ]
            ]
        ], self::getTree());

        $modelClass::find(6)->moveBefore(4);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 6, 'path' => 'Test6', 'order' => 4],
            ['id' => 4, 'path' => 'Test4', 'order' => 5,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 1],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 2],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(8)->moveBefore(3);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0],
            ['id' => 1, 'path' => 'Test1', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 8, 'path' => 'Test8', 'order' => 3],
            ['id' => 3, 'path' => 'Test3', 'order' => 4],
            ['id' => 6, 'path' => 'Test6', 'order' => 5],
            ['id' => 4, 'path' => 'Test4', 'order' => 6,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 1],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(9)->moveBefore(5);
        $this->assertEquals([
            ['id' => 9, 'path' => 'Test9', 'order' => 0],
            ['id' => 5, 'path' => 'Test5', 'order' => 1],
            ['id' => 1, 'path' => 'Test1', 'order' => 2],
            ['id' => 2, 'path' => 'Test2', 'order' => 3],
            ['id' => 8, 'path' => 'Test8', 'order' => 4],
            ['id' => 3, 'path' => 'Test3', 'order' => 5],
            ['id' => 6, 'path' => 'Test6', 'order' => 6],
            ['id' => 4, 'path' => 'Test4', 'order' => 7,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                ]
            ]
        ], self::getTree());

        $modelClass::find(7)->moveBefore(4);
        $this->assertEquals([
            ['id' => 9, 'path' => 'Test9', 'order' => 0],
            ['id' => 5, 'path' => 'Test5', 'order' => 1],
            ['id' => 1, 'path' => 'Test1', 'order' => 2],
            ['id' => 2, 'path' => 'Test2', 'order' => 3],
            ['id' => 8, 'path' => 'Test8', 'order' => 4],
            ['id' => 3, 'path' => 'Test3', 'order' => 5],
            ['id' => 6, 'path' => 'Test6', 'order' => 6],
            ['id' => 7, 'path' => 'Test7', 'order' => 7],
            ['id' => 4, 'path' => 'Test4', 'order' => 8],
        ], self::getTree());        
    }
    
    /**
     * @return void
     */
    public function testTreeableMoveAfterOneLevelUp()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5']
        ]);
        $modelClass = static::$modelClass;

        $modelClass::find(1)->moveAfter(5);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 4, 'path' => 'Test4', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3],
            ['id' => 1, 'path' => 'Test1', 'order' => 4],
        ], self::getTree());
        
        $modelClass::find(3)->moveAfter(1);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2],
            ['id' => 1, 'path' => 'Test1', 'order' => 3],
            ['id' => 3, 'path' => 'Test3', 'order' => 4],
        ], self::getTree());
        
        $modelClass::find(1)->moveAfter(3);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 1, 'path' => 'Test1', 'order' => 4],
        ], self::getTree());

        $modelClass::find(1)->moveAfter(3);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 4, 'path' => 'Test4', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 1, 'path' => 'Test1', 'order' => 4],
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveAfterOneLevelDown()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5']
        ]);
        $modelClass = static::$modelClass;

        $modelClass::find(5)->moveAfter(1);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 5, 'path' => 'Test5', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4],
        ], self::getTree());
        
        $modelClass::find(3)->moveAfter(1);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2],
            ['id' => 2, 'path' => 'Test2', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4],
        ], self::getTree());
        
        $modelClass::find(2)->moveAfter(3);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4],
        ], self::getTree());

        $modelClass::find(3)->moveAfter(1);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4],
        ], self::getTree());
    }

    /**
     * @return void
     */
    public function testTreeableMoveAfterDownLevel() {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5'],
            ['name' => 'Test6', 'parent_id' => 5],
            ['name' => 'Test7', 'parent_id' => 5],
            ['name' => 'Test8', 'parent_id' => 5],
            ['name' => 'Test9', 'parent_id' => 5],
        ]);
        $modelClass = static::$modelClass;

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3],
            ['id' => 5, 'path' => 'Test5', 'order' => 4,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                ]
            ]
                ], self::getTree());

        $modelClass::find(1)->moveAfter(9);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 4, 'path' => 'Test4', 'order' => 2],
            ['id' => 5, 'path' => 'Test5', 'order' => 3,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 4],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(4)->moveAfter(1);
        $this->assertEquals([
            ['id' => 2, 'path' => 'Test2', 'order' => 0],
            ['id' => 3, 'path' => 'Test3', 'order' => 1],
            ['id' => 5, 'path' => 'Test5', 'order' => 2,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 4],
                    ['id' => 4, 'path' => 'Test5/Test4', 'order' => 5],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->moveAfter(6);
        $this->assertEquals([
            ['id' => 3, 'path' => 'Test3', 'order' => 0],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 1],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 3],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 4],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 5],
                    ['id' => 4, 'path' => 'Test5/Test4', 'order' => 6],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(3)->moveAfter(8);
        $this->assertEquals([
            ['id' => 5, 'path' => 'Test5', 'order' => 0,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 1],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 3],
                    ['id' => 3, 'path' => 'Test5/Test3', 'order' => 4],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 5],
                    ['id' => 1, 'path' => 'Test5/Test1', 'order' => 6],
                    ['id' => 4, 'path' => 'Test5/Test4', 'order' => 7],
                ]
            ]
        ], self::getTree());        
    }

    /**
     * @return void
     */
    public function testTreeableMoveAfterBetweenLevel() {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2', 'parent_id' => 1],
            ['name' => 'Test3', 'parent_id' => 1],
            ['name' => 'Test4', 'parent_id' => 1],
            ['name' => 'Test5'],
            ['name' => 'Test6', 'parent_id' => 5],
            ['name' => 'Test7', 'parent_id' => 5],
            ['name' => 'Test8', 'parent_id' => 5],
            ['name' => 'Test9', 'parent_id' => 5],
        ]);
        $modelClass = static::$modelClass;

        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 0],
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 1],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 2],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->moveAfter(9);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 0],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 1],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 6, 'path' => 'Test5/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 3],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 4],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(6)->moveAfter(3);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 0],
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 1],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 2],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 1],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 2],
                    ['id' => 2, 'path' => 'Test5/Test2', 'order' => 3],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(2)->moveAfter(4);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 0],
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 1],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 2],
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 3],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test5/Test8', 'order' => 1],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 2],
                ]
            ]
        ], self::getTree());
        
        $modelClass::find(8)->moveAfter(6);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 3, 'path' => 'Test1/Test3', 'order' => 0],
                    ['id' => 6, 'path' => 'Test1/Test6', 'order' => 1],
                    ['id' => 8, 'path' => 'Test1/Test8', 'order' => 2],
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 3],
                    ['id' => 2, 'path' => 'Test1/Test2', 'order' => 4],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 1,
                'children' => [
                    ['id' => 7, 'path' => 'Test5/Test7', 'order' => 0],
                    ['id' => 9, 'path' => 'Test5/Test9', 'order' => 1],
                ]
            ]
        ], self::getTree());        
    }

    /**
     * @return void
     */
    public function testTreeableMoveAfterUpLevel()
    {
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4'],
            ['name' => 'Test5', 'parent_id' => 4],
            ['name' => 'Test6', 'parent_id' => 4],
            ['name' => 'Test7', 'parent_id' => 4],
            ['name' => 'Test8', 'parent_id' => 4],
            ['name' => 'Test9', 'parent_id' => 4],
        ]);
        $modelClass = static::$modelClass;
        
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3,
                'children' => [
                    ['id' => 5, 'path' => 'Test4/Test5', 'order' => 0],
                    ['id' => 6, 'path' => 'Test4/Test6', 'order' => 1],
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 2],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 3],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 4],
                ]
            ]
        ], self::getTree());

        $modelClass::find(5)->moveAfter(4);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2],
            ['id' => 4, 'path' => 'Test4', 'order' => 3,
                'children' => [
                    ['id' => 6, 'path' => 'Test4/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 1],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 2],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 3],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 4],
        ], self::getTree());

        $modelClass::find(6)->moveAfter(1);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 6, 'path' => 'Test6', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 1],
                    ['id' => 9, 'path' => 'Test4/Test9', 'order' => 2],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 5],
        ], self::getTree());
        
        $modelClass::find(9)->moveAfter(5);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 6, 'path' => 'Test6', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 3, 'path' => 'Test3', 'order' => 3],
            ['id' => 4, 'path' => 'Test4', 'order' => 4,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                    ['id' => 8, 'path' => 'Test4/Test8', 'order' => 1],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 5],
            ['id' => 9, 'path' => 'Test9', 'order' => 6],
        ], self::getTree());
        
        $modelClass::find(8)->moveAfter(2);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 6, 'path' => 'Test6', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 8, 'path' => 'Test8', 'order' => 3],
            ['id' => 3, 'path' => 'Test3', 'order' => 4],
            ['id' => 4, 'path' => 'Test4', 'order' => 5,
                'children' => [
                    ['id' => 7, 'path' => 'Test4/Test7', 'order' => 0],
                ]
            ],
            ['id' => 5, 'path' => 'Test5', 'order' => 6],
            ['id' => 9, 'path' => 'Test9', 'order' => 7],
        ], self::getTree());

        $modelClass::find(7)->moveAfter(4);
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0],
            ['id' => 6, 'path' => 'Test6', 'order' => 1],
            ['id' => 2, 'path' => 'Test2', 'order' => 2],
            ['id' => 8, 'path' => 'Test8', 'order' => 3],
            ['id' => 3, 'path' => 'Test3', 'order' => 4],
            ['id' => 4, 'path' => 'Test4', 'order' => 5],
            ['id' => 7, 'path' => 'Test7', 'order' => 6],
            ['id' => 5, 'path' => 'Test5', 'order' => 7],
            ['id' => 9, 'path' => 'Test9', 'order' => 8],
        ], self::getTree());
    }

    /**
     *
     * @return void
     */
    public function testTreeableSelect()
    {
        DB::enableQueryLog();
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
            ['name' => 'Test3'],
            ['name' => 'Test4', 'parent_id' => 1],
            ['name' => 'Test5', 'parent_id' => 1],
            ['name' => 'Test6', 'parent_id' => 3],
            ['name' => 'Test7', 'parent_id' => 3],
            ['name' => 'Test8', 'parent_id' => 3],
            ['name' => 'Test9', 'parent_id' => 7],
        ]);
        $modelClass = static::$modelClass;
        
        $this->assertEquals([
            ['id' => 1, 'path' => 'Test1', 'order' => 0,
                'children' => [
                    ['id' => 4, 'path' => 'Test1/Test4', 'order' => 0],
                    ['id' => 5, 'path' => 'Test1/Test5', 'order' => 1],
                ]
            ],
            ['id' => 2, 'path' => 'Test2', 'order' => 1],
            ['id' => 3, 'path' => 'Test3', 'order' => 2,
                'children' => [
                    ['id' => 6, 'path' => 'Test3/Test6', 'order' => 0],
                    ['id' => 7, 'path' => 'Test3/Test7', 'order' => 1,
                        'children' => [
                            ['id' => 9, 'path' => 'Test3/Test7/Test9', 'order' => 0],
                        ]
                    ],
                    ['id' => 8, 'path' => 'Test3/Test8', 'order' => 2],
                ]
            ]
        ], self::getTree());

        $this->assertEquals([
            ['value' => 1, 'label' => 'Test1'],
            ['value' => 4, 'label' => '&nbsp;&nbsp;&nbsp;Test4'],
            ['value' => 5, 'label' => '&nbsp;&nbsp;&nbsp;Test5'],
            ['value' => 2, 'label' => 'Test2'],
            ['value' => 3, 'label' => 'Test3'],
            ['value' => 6, 'label' => '&nbsp;&nbsp;&nbsp;Test6'],
            ['value' => 7, 'label' => '&nbsp;&nbsp;&nbsp;Test7'],
            ['value' => 9, 'label' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Test9'],
            ['value' => 8, 'label' => '&nbsp;&nbsp;&nbsp;Test8'],
        ], (new $modelClass())->getSelectTree());

        $this->assertEquals(53, count(DB::getQueryLog()));
    }
}
