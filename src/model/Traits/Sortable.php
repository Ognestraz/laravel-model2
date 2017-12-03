<?php namespace Model\Traits;

trait Sortable
{
    public function scopeSort($query, $by = 'asc')
    {
        return $query->orderBy('order', $by);
    }
    
    protected static function sortableNormalize()
    {
        $i = 0;
        foreach (self::sort()->get() as $model) {
            $model->order = $i++;
            $model->save();
        }        
    }

    protected static function bootSortable()
    {
        static::creating(function($model) {
            $order = self::all()->max('order');
            if (null === $order) {
                $model->order = 0;
            } else {
                $model->order = ++$order;
            }
            
            self::sortableNormalize();
        });

        static::deleted(function($model) {
            self::sortableNormalize();
        });        
    }
}
