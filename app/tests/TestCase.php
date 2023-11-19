<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // foi incluído o RefreshDatabase para reset dos bancos após cada teste
    use CreatesApplication, RefreshDatabase;
}
