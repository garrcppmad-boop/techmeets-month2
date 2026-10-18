<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テストではフロントエンドのビルド（public/build）が無くても画面を表示できるようにする
        $this->withoutVite();
    }
}
