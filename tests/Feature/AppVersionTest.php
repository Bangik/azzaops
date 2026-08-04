<?php

namespace Tests\Feature;

use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'apk_file_path' => 'apks/app-v1.apk',
        ]);

        $latest = AppVersion::create([
            'version_code' => 2,
            'version_name' => '1.0.1',
            'release_notes' => 'Bug fixes',
            'apk_file_path' => 'apks/app-v2.apk',
        ]);

        $response = $this->getJson(route('api.app-version.latest'));

        $response->assertStatus(200)
            ->assertJson([
                'version_code' => 2,
                'version_name' => '1.0.1',
                'release_notes' => 'Bug fixes',
                'download_url' => asset(Storage::url('apks/app-v2.apk')),
            ]);
    }
}
