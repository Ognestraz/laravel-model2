<?php namespace Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;
    use Traits\Act;
    use Traits\Treeable;
    use Traits\Menuable;
    use Traits\Imageable;

    protected $table = 'site';
    protected $visible = [
        'id',
        'name',
        'order',
        'parent_id',
        'path',
        'view',
        'preview',
        'content'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'act',
        'order',
        'path',
        'view',
        'parent_id',
        'name',
        'preview',
        'content',
    ];
}
