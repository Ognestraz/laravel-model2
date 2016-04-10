<?php namespace Model;

use ReflectionClass;

class Model extends \Illuminate\Database\Eloquent\Model 
{
    protected $validationMessages = null;
    static public $side = null;

    static public $rules = array();    
    static public $messages = array();   
    
    protected $error = array();
    
    protected static function boot()
    {
        parent::boot();
        $class = new ReflectionClass(static::class);
        $traits = $class->getTraitNames();
        foreach ($traits as $trait) {
            $methodName = 'boot' . substr(strrchr($trait, '\\'), 1);
            if (method_exists(static::class, $methodName)) {
                static::$methodName();
            }
        }
    }    
    
    static public function get($model, $id = null) 
    {
        if (!empty($model)) {
            $model_class = 'Model\\' . ucfirst($model);
            return !empty($id) || is_numeric($id) ? $model_class::find($id) : new $model_class();
        }
        
        return null;
    }
}
