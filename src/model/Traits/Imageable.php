<?php namespace Model\Traits;

use Model\Image;

trait Imageable {
    /**
     * Get all of the
     */
    public function image()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function addImage($id)
    {
        $image = Image::find($id);
        $this->image()->save($image);
    }    
}
