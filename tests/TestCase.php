<?php

namespace Ognestraz\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public static function createItems($nodes, $modelClass = null)
    {
        if (null === $modelClass) {
            $modelClass = static::$modelClass;
        }
        foreach ($nodes as $node) {
            $model = new $modelClass();
            foreach ($node as $field => $value) {
                $model->$field = $value;
            }
            $model->save();            
        }
    }
}
