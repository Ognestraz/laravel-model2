<?php

namespace Ognestraz\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Model\Image;
use Ognestraz\Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    static protected $modelClass = Image::class;

    public function setUp()
    {
        parent::setUp();
        foreach (Storage::files() as $file) {
            Storage::delete($file);
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicCreate()
    {
        $uploadedFile = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);

        $image = new Image();
        $image->name = 'New1';
        $image->file = $uploadedFile;
        $image->save();

        $newImage = Image::find(1);
        $this->assertEquals('New1', $newImage->name);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(3008, Storage::size($newImage->path));

        Storage::assertExists($newImage->path);
        Storage::get($newImage->path);
    }
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicUpdate()
    {
        $uploadedFileCreate = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);
        $uploadedFileUploaded = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $image = new Image();
        $image->name = 'New1';
        $image->file = $uploadedFileCreate;
        $image->save();

        $newImage = Image::find(1);
        $firstNewImagePath = $newImage->path;
        $this->assertEquals('New1', $newImage->name);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(3008, Storage::size($newImage->path));
        Storage::assertExists($newImage->path);
        Storage::get($newImage->path);
        
        $newImage->file = $uploadedFileUploaded;
        $newImage->save();
        
        $this->assertEquals($newImage->path, $firstNewImagePath);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(827, Storage::size($newImage->path));
        Storage::assertExists($newImage->path);
        Storage::get($newImage->path);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicDelete()
    {
        $uploadedFile = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);

        $image = new Image();
        $image->name = 'New1';
        $image->file = $uploadedFile;
        $image->save();

        $newImage = Image::find(1);
        $this->assertEquals('New1', $newImage->name);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(3008, Storage::size($newImage->path));

        $newImage->delete();
        Storage::assertExists($newImage->path);
        $this->assertEquals('private', Storage::getVisibility($newImage->path));
        Storage::get($newImage->path);

        $this->assertEquals(null, Image::find(1));
        $imageDeleted = Image::withTrashed()->where('id', 1)->first();
        $this->assertEquals(1, $imageDeleted->id);

        $newImage->forceDelete();
        Storage::assertMissing($newImage->path);

        $imageForceDeleted = Image::withTrashed()->where('id', 1)->first();
        $this->assertEquals(null, $imageForceDeleted);        
    }
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicRestore()
    {
        $uploadedFile = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);

        $image = new Image();
        $image->name = 'New1';
        $image->file = $uploadedFile;
        $image->save();

        $newImage = Image::find(1);
        $this->assertEquals('New1', $newImage->name);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(3008, Storage::size($newImage->path));

        $newImage->delete();
        Storage::assertExists($newImage->path);
        $this->assertEquals('private', Storage::getVisibility($newImage->path));
        Storage::get($newImage->path);

        $this->assertEquals(null, Image::find(1));
        $imageDeleted = Image::withTrashed()->where('id', 1)->first();
        $this->assertEquals(1, $imageDeleted->id);

        $imageDeleted->restore();
        Storage::assertExists($imageDeleted->path);
        $this->assertEquals('public', Storage::getVisibility($imageDeleted->path));

        $restoredImage = Image::find(1);
        $this->assertEquals(1, $restoredImage->id);
    }
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicRename()
    {
        $uploadedFile = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);

        $image = new Image();
        $image->name = 'New1';
        $image->file = $uploadedFile;
        $image->save();

        $newImage = Image::find(1);
        $imagePath = $newImage->path;
        $this->assertEquals('New1', $newImage->name);
        $this->assertEquals('/storage/' . $newImage->path, Storage::url($newImage->path));
        $this->assertEquals('public', Storage::getVisibility($newImage->path));
        $this->assertEquals(3008, Storage::size($newImage->path));

        $changePath = 'image' . md5(time()) . '.jpeg';
        $newImage->path = $changePath;
        $newImage->save();
        Storage::assertMissing($imagePath);

        $changedImage = Image::find(1);
        $this->assertEquals($changePath, $changedImage->path);
        Storage::assertExists($changedImage->path);
        $this->assertEquals('public', Storage::getVisibility($changedImage->path));
        Storage::get($changedImage->path);
        Storage::delete($changedImage->path);
    }    
}
