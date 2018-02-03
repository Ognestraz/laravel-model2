<?php namespace Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model {

    use SoftDeletes, Traits\Act, Traits\File;

    protected $table = 'images';
    protected $visible = [
        'id',
        'act',
        'path',
        'order',
        'name',
        'description',
        'filename'
    ];

    protected $softDelete = true;
    
    public $imageDir = 'public/files/image/';
    public $imageDirSrc = 'files/image/';

    public function getDirectoryPath() 
    {
        return base_path() . '/' . $this->imageDir;
    }
}
