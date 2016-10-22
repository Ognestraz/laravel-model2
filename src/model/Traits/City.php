<?php namespace Model\Traits;

trait City
{
    public function city()
    {
        return $this->hasOne(config('model.city') ?: 'Model\City', 'id', 'city_id');
    }
}
