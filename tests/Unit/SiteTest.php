<?php

namespace Ognestraz\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model\Site;
use Ognestraz\Tests\TestCase;

class SiteTest extends TestCase
{
    static protected $modelClass = Site::class;

    use RefreshDatabase;
    use Traits\Treeable;
    use Traits\Menuable;
    use Traits\Imageable;    
}
