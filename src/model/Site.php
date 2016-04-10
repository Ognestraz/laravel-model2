<?php namespace Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes, Traits\Menu, Traits\Tree, Traits\Seo, Traits\Path;
    use Traits\Image, Traits\Video;
    use Traits\Act, Traits\Sortable;
    
    static public $site = null;
    
    protected $table = 'site';
    protected $visible = array(
        'id',
        'name',
        'parent',
        'path',
        'content'
    );
 
    public function setSettingsAttribute($settings)
    {

    }  

    public function getSettingsAttribute($value)
    {
        return is_string($value) ? unserialize($value) : [];
    }    

    public function getSettings($param = null)
    {
        $return = empty($this->settings) ? self::find(1)->settings : $this->settings;
        return $return;
    }
    
    public function title()
    {
        return !empty($this->title) ? $this->title : $this->name;
    }

    public function getKeywords()
    {
        if (!empty($this->keywords)) {
            return $this->keywords;
        }

        return self::find(1)->keywords;
    }

    public function getDescription()
    {
        if (!empty($this->description)) {
            return $this->description;
        }

        return self::find(1)->description;
    }

    public function link() 
    {
        return url('/').'/'.$this->path;
    }
    
    public function template($template, $childsTemplate = null)
    {
        $this->template = $template;

        if (!empty($childsTemplate)) {

            foreach ($this->childs()->get() as $item) {

                $item->template = $childsTemplate;
                $item->save();

            }
            
        }

        $this->save();
        
        return $this->template;
    }
    
    public function view() 
    {
        return $this->template ? 'site.' . $this->template : 'site.show';
    }
}
