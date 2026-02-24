<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('logging.security_channel', 'null');
    }

    protected function fakePublicDisk(): string
    {
        $root = storage_path('framework/testing/disks/public_fake_'.Str::random(12));

        if (! is_dir($root)) {
            mkdir($root, 0777, true);
        }

        Storage::set('public', Storage::build([
            'driver' => 'local',
            'root' => $root,
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => true,
        ]));

        return $root;
    }
}
