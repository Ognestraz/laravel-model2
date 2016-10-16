<?php namespace Model\Traits;

trait City
{
    public function city()
    {
        return $this->hasOne("Model\City", 'id', 'city_id');
    }
}
