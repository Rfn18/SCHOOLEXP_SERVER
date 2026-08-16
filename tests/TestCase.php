<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    // Tambahkan method setUp ini
    protected function setUp(): void
    {
        parent::setUp();

        // Matikan Foreign Key Constraints khusus untuk database SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }
    }

    protected function jsonWithCookies(string $method, string $uri, array $cookies = [], array $data = [], array $headers = []): TestResponse
    {
        $server = [];
        foreach ($headers as $key => $value) {
            $server['HTTP_' . str_replace('-', '_', strtoupper($key))] = $value;
        }
        $server['HTTP_ACCEPT'] = 'application/json';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = empty($data) ? null : json_encode($data);

        $response = $this->call($method, $uri, [], $cookies, [], $server, $content);

        return TestResponse::fromBaseResponse($response);
    }
}
