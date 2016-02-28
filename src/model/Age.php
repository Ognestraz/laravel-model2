<?php namespace Model;

class Age extends Model
{
    use Traits\Act;
    
    public $timestamps = false;

    protected $table = 'age';
    protected $visible = array(
        'id',
        'act',
        'name',
        'short'
    );
}
