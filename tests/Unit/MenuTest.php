<?php

namespace Ognestraz\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model\Menu;
use Ognestraz\Tests\TestCase;

class MenuTest extends TestCase
{
    static protected $modelClass = Menu::class;

    use RefreshDatabase;
    use Traits\Treeable;
    use Traits\Imageable;
}
