<?php

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => config('app.api_url'),
            'timeout'  => 2.0,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function testSignup(): void {
        $params = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $json = $this->client->post("api/auth/signup", [
            'json' => $params,
        ]);

        $this->assertEquals(200, $json->getStatusCode());
    }

    public function testSignin(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => Hash::make('password'),
        ]);

        $json = $this->client->post("api/auth/signin", [
            'json' => [
                'email' => $user->email,
                'password' => 'password',
            ],
        ]);

        $response = json_decode($json->getBody(), true);

        $this->assertEquals(200, $json->getStatusCode());
        $this->assertArrayHasKey('token', $response);
    }
}
