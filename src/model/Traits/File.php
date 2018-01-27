<?php namespace Model\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Http\UploadedFile;

use Illuminate\Http\Testing\File as FileTest;

trait File {

    protected static function bootFile()
    {
        static::restored(function($model) {
            Storage::setVisibility($model->path, 'public');
        });

        static::updating(function($model) {
            if (true === $model->isDirty('path')) {
                Storage::move($model->getOriginal('path'), $model->path);
            }            
        });        

        static::deleted(function($model) {
            if ($model->isForceDeleting()) {
                Storage::delete($model->path);
            } else {
                Storage::setVisibility($model->path, 'private');
            }
        });        
    }

    public function setFileAttribute($file)
    {
        if ($file instanceof UploadedFile) {
            if (empty($this->attributes['path'])) {
                $this->attributes['path'] = self::genName($file);
            }
            Storage::put($this->attributes['path'], FileFacade::get($file), 'public');
        }
    }    

    public static function genName(UploadedFile $file) 
    {
        $fname = substr(md5($file->path().microtime()), 8, 12);
        $fname .= '.' . strtolower($file->extension());
        
        return $fname;
    }   
    
    public function src($part = '', $default = '') 
    {
        $part_path = $part ? $part.'/' : '';
        $filename = $this->imageDirSrc.$part_path.$this->path;

        return is_file($filename) ? URL::to('/').'/'.$filename : $default;
    }
    
    public function srcNoCache($part = '', $default = '')
    {
        return $this->src($part, $default).'?r='.rand();
    }
}
