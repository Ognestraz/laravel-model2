<?php namespace Model;

class Color extends Model
{
    use Traits\Act;
    
    public $timestamps = false;

    protected $table = 'color';
    protected $visible = array(
        'id',
        'act',
        'name',
        'code'
    );
}
