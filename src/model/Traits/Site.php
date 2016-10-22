<?php namespace Model\Traits;

trait Site
{
    public function sites()
    {
        return $this->hasMany(config('model.site') ?: 'Model\Site', 'site_id');
    }

    public function site()
    {
        return $this->hasOne(config('model.site') ?: 'Model\Site', 'id', 'site_id');
    }
}
