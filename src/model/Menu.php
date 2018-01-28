<?php namespace Model;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Request;

class Menu extends Model
{
    use SoftDeletes;
    use Traits\Treeable;
    use Traits\Act;
    use Traits\Imageable;

    protected $table = 'menu';
    protected $visible = [
        'id',
        'parent_id',
        'order',
        'name',
        'content',
        'path'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent_id',
        'path',
        'content'
    ];

    /**
     * Get all of the owning menuable models.
     */
    public function menuable()
    {
        return $this->morphTo();
    }

    public function link() 
    {
        return url('/').'/'.$this->path;
    }

    public function put(Model $model)
    {
        
        echo class_basename($model);
        
    }
    
}
