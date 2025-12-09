<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);
    }

    public function test_security_settings_are_saved_successfully()
    {
        $this->actingAs($this->admin);
        
        $data = [
            'password_min_length' => 10,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => false,
            'password_expiration_days' => 60,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals(10, SystemSetting::getSetting('password_min_length'));
        $this->assertTrue(SystemSetting::getSetting('password_require_uppercase'));
    }

    public function test_password_min_length_validation_too_short()
    {
        $this->actingAs($this->admin);

        $data = [
            'password_min_length' => 4,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => false,
            'password_expiration_days' => 90,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);
        $response->assertStatus(422)->assertJsonValidationErrors('password_min_length');
    }

    public function test_special_characters_requires_longer_password()
    {
        $this->actingAs($this->admin);

        $data = [
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => true,
            'password_expiration_days' => 90,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);
        $response->assertStatus(422)->assertJsonValidationErrors('password_min_length');
    }

    public function test_password_expiration_days_validation()
    {
        $this->actingAs($this->admin);

        $data = [
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => false,
            'password_expiration_days' => -1,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);
        $response->assertStatus(422)->assertJsonValidationErrors('password_expiration_days');
    }

    public function test_security_settings_sync_after_save()
    {
        $this->actingAs($this->admin);

        $firstUpdate = [
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_numbers' => false,
            'password_require_special' => false,
            'password_expiration_days' => 90,
        ];

        $response1 = $this->postJson(route('admin.configuraciones.seguridad'), $firstUpdate);
        $response1->assertStatus(200);

        $this->assertEquals(8, SystemSetting::getSetting('password_min_length'));
        $this->assertFalse(SystemSetting::getSetting('password_require_numbers'));

        $secondUpdate = [
            'password_min_length' => 12,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => true,
            'password_expiration_days' => 60,
        ];

        $response2 = $this->postJson(route('admin.configuraciones.seguridad'), $secondUpdate);
        $response2->assertStatus(200);

        $this->assertEquals(12, SystemSetting::getSetting('password_min_length'));
        $this->assertTrue(SystemSetting::getSetting('password_require_numbers'));
    }

    public function test_non_admin_cannot_access_security_settings()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $data = [
            'password_min_length' => 8,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => false,
            'password_expiration_days' => 90,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);
        $response->assertStatus(403);
    }

    public function test_response_json_structure()
    {
        $this->actingAs($this->admin);

        $data = [
            'password_min_length' => 9,
            'password_require_uppercase' => true,
            'password_require_numbers' => true,
            'password_require_special' => false,
            'password_expiration_days' => 75,
        ];

        $response = $this->postJson(route('admin.configuraciones.seguridad'), $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'password_min_length',
                    'password_require_uppercase',
                    'password_require_numbers',
                    'password_require_special',
                    'password_expiration_days',
                    'updated_at'
                ],
                'timestamp'
            ]);
    }
}
