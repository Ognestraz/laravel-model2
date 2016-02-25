<?php namespace Model;

class Country extends Model
{
    use Traits\Act;
    
    protected $table = 'country';
    protected $visible = array(
        'id',
        'act',
        'name',
        'short'
    );
}
