<?php namespace Model\Traits;

trait Path
{
    static public function findPath($path, $act = true, $fail = false)
    {
        $model = self::where('path', '=', $path == '/' ? '' : $path);
        if (isset($act)) {
            $model->where('act', $act);
        }
        return $fail ? $model->firstOrFail() : $model->first();
    }

    protected static function bootPath()
    {
        static::creating(function($model) {
            if (!$model->path) {
                //$path = translite($model->name);
                $path = $model->name;
//                if ($model->parent) {
//                    $parent = $model->parent();
//                    if ($parent->path) {
//                        $model->path = $parent->path . '/' . $path;
//                    }
//                } else {
                    $model->path = $path;                    
//                }
            }
        });
    } 

}
