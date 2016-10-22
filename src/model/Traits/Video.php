<?php namespace Model\Traits;

trait Video
{
    public function videos($part = null)
    {
        $model = $this->hasMany(config('model.video') ?: 'Model\Video', 'model_id');
        return !empty($part) ? $model->where('part', $part) : $model;
    }
    
    public function video()
    {
        return $this->videos('main')->orderBy('sort', 'asc')->first();
    }
}
