<?php namespace Model;

class City extends Model
{
    use Traits\Act;
    
    public $timestamps = false;

    protected $table = 'city';
    protected $visible = array(
        'id',
        'act',
        'country_id',
        'name',
        'short'
    );
}
