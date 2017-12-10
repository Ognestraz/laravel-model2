<?php namespace Model\Traits;

trait Path
{
    static public function findPath($path)
    {
        return self::where('path', $path);
    }

    protected static function bootPath()
    {
        static::saving(function($model) {
            if (null === $model->path) {
                $model->path = $model->name;
            }

            if (true === $model->isDirty('parent_id')) {
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
        });
    }

}
