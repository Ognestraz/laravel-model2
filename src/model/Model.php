<?php namespace Model;

use ReflectionClass;

class Model extends \Illuminate\Database\Eloquent\Model 
{
    protected $validationMessages = null;
    static public $side = null;

    protected static function boot()
    {
        parent::boot();
//        $class = new ReflectionClass(static::class);
//        $traits = $class->getTraitNames();
//        foreach ($traits as $trait) {
//            $methodName = 'boot' . substr(strrchr($trait, '\\'), 1);
//            if (method_exists(static::class, $methodName)) {
//                static::$methodName();
//            }
//        }
    }    
    
    static public function get($model, $id = null) 
    {
        if (!empty($model)) {
            $nameModel = ucfirst($model);
            $classModel = config("model.{$nameModel}") ?: "Model\{$nameModel}";
            return null !== $id ? $classModel::find($id) : new $classModel();
        }
        
        return null;
    }
}
