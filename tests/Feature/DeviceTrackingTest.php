<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_upsert_device_endpoint(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'device_id' => 'device-test-123',
            'platform' => 'android',
            'app_version' => '1.0.0',
            'build_number' => 1,
            'os_version' => '13',
            'device_brand' => 'Google',
            'device_model' => 'Pixel 7',
            'screen_resolution' => '1080x2400',
            'network_type' => 'wifi',
            'session_id' => 'session-123'
        ];

        $response = $this->postJson(route('api.devices.upsert'), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_devices', [
            'device_id' => 'device-test-123',
            'user_id' => $user->id
        ]);
    }
}
