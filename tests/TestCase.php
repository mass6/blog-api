<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use DatabaseMigrations;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://blog-api.app';

    /** @var  User */
    protected $user;

    protected $token;

    protected $headers;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     |
     */

    protected function getHeaders()
    {
        return $this->headers;
    }

    protected function getHeadersWithToken()
    {
        return array_merge($this->headers, ['Authorization' => 'Bearer '.$this->user->api_token]);
    }


}
