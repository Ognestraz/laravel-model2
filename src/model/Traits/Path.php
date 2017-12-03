<?php namespace Model\Traits;

trait Path
{
    static public function findPath($path)
    {
        return self::where('path', $path);
    }

    protected static function bootPath()
    {
        static::creating(function($model) {
            if (null === $model->path) {
                $model->path = $model->name;
            }
        });
        
        static::saving(function($model) {
            if (true === $model->isDirty('parent_id')) {
                $part = explode('/', $model->path);
                $path = end($part);
                
                if ($model->parent_id) {
                    $parent = $model->getParent();
                    if ($parent->path) {
                        $model->path = ('/' !== $parent->path) ? $parent->path . '/' . $path : '/' . $path;
                    } else {
                        $model->path = $path;
                    }
                } else {
                    $model->path = $path;                    
                }
            }
        });
    }

}
