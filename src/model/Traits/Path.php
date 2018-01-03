<?php namespace Model\Traits;

use Illuminate\Support\Facades\Log;

trait Path
{
    static public function findPath($path)
    {
        return self::where('path', $path);
    }

    public function endPath()
    {
        $part = explode('/', $this->path);
        return end($part);
    }

    protected static function eventSaving($model)
    {
        if (null === $model->path) {
            $model->path = $model->name;
        }

        if (true === $model->isDirty('parent_id')) {
            Log::info('Dirty parent_id before:', [$model->toArray(), $model->getOriginal()]);
            
            $part = explode('/', $model->path);
            $path = end($part);

            if ($model->parent_id) {
                $parent = $model->getParent();
                if ($parent->path) {
                    $model->path = ('/' !== $parent->path) ? $parent->path . '/' . $path : '/' . $path;
                } elseif ($path) {
                    $model->path = $path;
                }

                $oldParent = self::find($model->getOriginal('parent_id'));
                $oldParentPath = '';
                if (null !== $oldParent) {
                    $oldParentPath = $oldParent->path;
                }

                foreach ($model->childs()->get() as $item) {
                    $item->path = substr_replace($item->path, $parent->path, 0, strlen($oldParentPath));
                    Log::info('Item path', [$item->toArray(), $item->getOriginal()]);
                    $item->save();
                }

            } elseif ($path) {
                $model->path = $path;                    
            }
            
            Log::info('Dirty parent_id after:', [$model->toArray(), $model->getOriginal()]);
        }
        
        if (true === $model->isDirty('path') && true !== $model->isDirty('parent_id')) {
            Log::info('Dirty path', [$model->toArray(), $model->getOriginal()]);
            foreach ($model->childs()->get() as $item) {
                $item->path = $model->path . '/' . $item->endPath();
                Log::info('Sub Item path', [$item->toArray(), $item->getOriginal()]);
                $item->save();
            }
        }
    }

    protected static function bootPath()
    {
        Log::info('');
        static::creating(function($model) {
            Log::info('Event: Creating', $model->toArray());
            static::eventSaving($model);
        });

        static::updating(function($model) {
            Log::info('Event: Updating', $model->toArray());
            static::eventSaving($model);
        });
    }

}
