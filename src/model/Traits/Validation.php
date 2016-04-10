<?php namespace Model\Traits;

trait Validation
{
    static public $rules = [];    
    static public $messages = [];   
    protected $error = [];    
    
    public function getError() 
    {
        return $this->error;
    }
    
    public function getRules() 
    {
        $rules = empty(static::$rules) ? self::$rules : array_merge(self::$rules, static::$rules);
        foreach ($rules as &$value) {
            $value = strtr($value, ['{id}' => $this->id]);
        }
        
        return $rules;
    }
    
    public function getMessages() 
    {
        return empty(static::$messages) ? self::$messages : array_merge(self::$messages, static::$messages);
    }
    
    public function validate($input = null, $rules = null, $messages = null) 
    {
        if (is_null($input)) {
            
            $input = Input::all();
            
        }
        
        if (is_null($rules)) {
            
            $rules = $this->getRules();
            
        }
        
        if (is_null($messages)) {
            
            $messages = $this->getMessages();
            
        }
        $v = Validator::make($input, $rules, $messages);
        if ($v->passes()) {
            
            return true;
            
        } else {
            
            Input::flash();
            
            foreach ($input as $key => $value) {
                
                $error = $v->messages()->get($key);
                
                if ($error) {
                
                    $this->validationMessages[$key] = $error;
                }
                
            }
            
            $this->error = $v->errors();
            
            return false;
            
        }
    }    
    
    public function save(array $options = array()) 
    {
        if ($this->validate() && !$this->validationMessages) {
            
            return parent::save($options);
        
        }
        
        return false;
    }
    
    public function getValidationMessages() 
    {
        return $this->validationMessages;
    }    

}
