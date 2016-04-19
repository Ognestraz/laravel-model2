<?php namespace Model\Traits;

trait Site
{
    public function sites()
    {
        return $this->hasMany("Model\Site", 'site_id');
    }

    public function site()
    {
        return $this->hasOne("Model\Site", 'id', 'site_id');
    }
}
