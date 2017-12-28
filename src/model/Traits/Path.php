<?php namespace Model\Traits;

trait Path
{
    static public function findPath($path)
    {
        return self::where('path', $path);
    }

    protected static function eventSaving($model)
    {
        if (null === $model->path) {
            $model->path = $model->name;
        }
        
        if (true === $model->isDirty('parent_id')/* && true !== $model->isDirty('path')*/) {
            $part = explode('/', $model->path);
            $path = end($part);

            if ($model->parent_id) {
                $parent = $model->getParent();
                if ($parent->path) {
                    $model->path = ('/' !== $parent->path) ? $parent->path . '/' . $path : '/' . $path;
                } elseif ($path) {
                    $model->path = $path;
                }
            } elseif ($path) {
                $model->path = $path;                    
            }
        }
    }

    protected static function bootPath()
    {
        static::creating(function($model) {
            static::eventSaving($model);
        });

        static::updating(function($model) {
            static::eventSaving($model);
        });
    }

}
