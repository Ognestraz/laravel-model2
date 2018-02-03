<?php

namespace Ognestraz\Tests\Unit\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Model\Image;

trait Imageable
{
    /**
     *
     * @return void
     */
    public function testImageableOneItemOneImage()
    {
        DB::enableQueryLog();
        $modelClass = self::$modelClass;

        self::createItems([
            ['name' => 'Test1']
        ], $modelClass);

        self::createItems([
            ['file' => UploadedFile::fake()->image('avatar.jpg', 1000, 1000)]
        ], Image::class);

        $model = $modelClass::find(1);
        $this->assertEquals(1, $model->id);
        $this->assertEquals([], $model->images->toArray());

        $image = Image::find(1);
        $this->assertEquals(1, $image->id);
        $model->addImage(1);

        $imageSite = $modelClass::find(1);
        $this->assertEquals([[
            'id' => 1,
            'act' => 0,
            'order' => 0,
            'name' => null,
            'description' => null,
            'path' => $image->path
        ]], $imageSite->images->toArray());

        $this->assertEquals(10, count(DB::getQueryLog()));
    }

    /**
     *
     * @return void
     */
    public function testImageableOneItemTwoImage()
    {
        DB::enableQueryLog();
        $modelClass = self::$modelClass;
        
        self::createItems([
            ['name' => 'Test1']
        ], $modelClass);

        self::createItems([
            ['file' => UploadedFile::fake()->image('avatar.jpg', 1000, 1000)],
            ['file' => UploadedFile::fake()->image('avatar.jpg', 1000, 1000)],
        ], Image::class);

        $model = $modelClass::find(1);
        $this->assertEquals([], $model->images->toArray());
        $model->addImage(1);
        $model->addImage(2);

        $imageSite = $modelClass::find(1);
        $this->assertEquals([
            [
                'id' => 1,
                'act' => 0,
                'name' => null,
                'order' => 0,
                'description' => null,
                'path' => Image::find(1)->path
            ],
            [
                'id' => 2,
                'act' => 0,
                'name' => null,
                'order' => 0,
                'description' => null,
                'path' => Image::find(2)->path
            ],            
        ], $imageSite->images->toArray());

        $this->assertEquals(14, count(DB::getQueryLog()));
    }
    
    /**
     *
     * @return void
     */
    public function testImageableTwoItemOneImage()
    {
        DB::enableQueryLog();
        $modelClass = self::$modelClass;
        
        self::createItems([
            ['name' => 'Test1'],
            ['name' => 'Test2'],
        ], $modelClass);

        self::createItems([
            ['file' => UploadedFile::fake()->image('avatar.jpg', 1000, 1000)]
        ], Image::class);

        $modelClass::find(1)->addImage(1);
        $this->assertEquals([
            [
                'id' => 1,
                'act' => 0,
                'name' => null,
                'order' => 0,
                'description' => null,
                'path' => Image::find(1)->path
            ]           
        ], $modelClass::find(1)->images->toArray());
        
        $modelClass::find(2)->addImage(1);
        $this->assertEquals([], $modelClass::find(1)->images->toArray());
        $this->assertEquals([
            [
                'id' => 1,
                'act' => 0,
                'name' => null,
                'order' => 0,
                'description' => null,
                'path' => Image::find(1)->path
            ]           
        ], $modelClass::find(2)->images->toArray());

        $this->assertEquals(19, count(DB::getQueryLog()));
    }
}
