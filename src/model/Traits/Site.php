<?php namespace Model\Traits;

trait Site
{
    public function site()
    {
        return $this->hasMany("Model\Site", 'site_id');
    }
}
