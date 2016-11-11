<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class PassportTestCase extends TestCase
{
    use DatabaseMigrations;

    /** @var  User */
    protected $user;

    protected $token;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->token = $this->getAccessToken($this->user);
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
        ];
    }


    /*
     |--------------------------------------------------------------------------
     | Utility Methods
     |--------------------------------------------------------------------------
     |
     */

    /**
     * Creates a Password Grant Client and returns the access token
     *
     * @param User $user
     * @return
     */
    protected function getAccessToken(User $user)
    {
        $this->artisan('passport:install');
        $PasswordGrantClient = DB::table('oauth_clients')->where('name', 'Laravel Password Grant Client')->get()->first();

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $PasswordGrantClient->id,
            'client_secret' => $PasswordGrantClient->secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => '',
        ]);
        $response = json_decode($this->response->getContent(), true);

        return $response['access_token'];
    }
}
