<?php

namespace Tests\Feature;

use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_latest_app_version_empty(): void
    {
        $response = $this->getJson(route('api.app-version.latest'));
        $response->assertStatus(404);
    }

    public function test_get_latest_app_version(): void
    {
        AppVersion::create([
            'version_code' => 1,
            'version_name' => '1.0.0',
            'apk_url' => 'https://drive.google.com/file/d/12345/view',
        ]);

        $latest = AppVersion::create([
            'version_code' => 2,
            'version_name' => '1.0.1',
            'release_notes' => 'Bug fixes',
            'apk_url' => 'https://drive.google.com/file/d/67890/view',
        ]);

        $response = $this->getJson(route('api.app-version.latest'));

        $response->assertStatus(200)
            ->assertJson([
                'version_code' => 2,
                'version_name' => '1.0.1',
                'release_notes' => 'Bug fixes',
                'download_url' => 'https://drive.google.com/file/d/67890/view',
            ]);
    }
}
