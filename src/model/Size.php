<?php namespace Model;

class Size extends Model
{
    use Traits\Act;
    
    public $timestamps = false;

    protected $table = 'size';
    protected $visible = array(
        'id',
        'act',
        'sort',
        'name',
        'label',
        'min',
        'max'
    );
}
