<?php namespace Model\Traits;

use Model\Image;

trait Imageable {
    /**
     * Get all of the
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function addImage($id)
    {
        $image = Image::find($id);
        $this->images()->save($image);
    }    
}
