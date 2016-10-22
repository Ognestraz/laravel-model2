<?php namespace Model\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;

trait File
{
    public function files($part = null)
    {
        $model = $this->hasMany(config('model.file') ?: 'Model\File', 'model_id');
        return !empty($part) ? $model->where('part', $part) : $model;
    }
    
    public function getFirstFile()
    {
        return $this->files('main')->orderBy('sort', 'asc')->first();
    }
    
    public function setFilenameAttribute($filename)
    {
        $oldFileName = !empty($this->attributes['filename']) ? $this->attributes['filename'] : '';
        if (class_basename($filename) == 'UploadedFile') {
            $this->attributes['filename'] = self::genName($filename);
            $this->attributes['path'] = $this->attributes['filename'];
            Storage::disk('local')->put($this->attributes['filename'],  FileFacade::get($filename));
            
            if ($oldFileName && Storage::exists($oldFileName)) {
                Storage::delete($oldFileName);
            }
        } else if(is_string($filename) && is_file($filename)) {
            $new_filename = basename($filename);
        } else if ($filename && is_string($filename) && $oldFileName && $oldFileName != $filename) {
            Storage::move($oldFileName, $filename);
        } else {
            $this->attributes['filename'] = $filename;
        }
    }    
    
     public static function genName($file) 
     {
        $fname = substr(md5($file->getClientOriginalName().microtime()), 8, 12);
        $fname .= '.' . strtolower($file->getClientOriginalExtension());
        
        return $fname;
    }
}