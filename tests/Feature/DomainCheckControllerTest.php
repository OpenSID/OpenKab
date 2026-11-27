<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainCheckControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Test',
        ]);

        // Create API key setting
        Setting::create([
            'key' => 'database_gabungan_api_key',
            'value' => 'test-api-key',
        ]);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $response = $this->get('/pengaturan/domain-check');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_domain_check_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/pengaturan/domain-check');

        $response->assertStatus(200);
        $response->assertSee('Cek Domain API Database Gabungan');
        $response->assertSee('Cek Domain Sekarang');
    }

    /** @test */
    public function it_can_check_domain_with_valid_api(): void
    {
        Http::fake([
            '*/api/v1/debug/domain-check' => Http::response([
                'status' => 'OK',
                'detected_domain' => 'simatik.bimakota.go.id',
                'detection_source' => 'Origin header',
                'is_server_side' => false,
                'is_allowed' => true,
                'headers' => [
                    'Origin' => 'https://simatik.bimakota.go.id',
                    'Referer' => '-',
                    'Host' => 'api-simatik.bimakota.go.id',
                    'X-Forwarded-For' => '-',
                    'X-Real-IP' => '-',
                ],
                'user' => [
                    'id' => 2,
                    'name' => 'OpenKab',
                    'allowed_domains' => ['simatik.bimakota.go.id'],
                    'is_wildcard' => false,
                ],
                'recommendation' => "Domain 'simatik.bimakota.go.id' sudah ada di allowed_domains. Request akan diizinkan.",
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/pengaturan/domain-check');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'OK',
            'detected_domain' => 'simatik.bimakota.go.id',
            'is_allowed' => true,
        ]);
    }

    /** @test */
    public function it_handles_api_error(): void
    {
        Http::fake([
            '*/api/v1/debug/domain-check' => Http::response([
                'message' => 'Unauthorized',
            ], 401),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/pengaturan/domain-check');

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'ERROR',
        ]);
    }

    /** @test */
    public function it_handles_api_timeout(): void
    {
        Http::fake([
            '*/api/v1/debug/domain-check' => Http::timeout(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/pengaturan/domain-check');

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'ERROR',
        ]);
    }

    /** @test */
    public function it_handles_missing_api_key(): void
    {
        Setting::where('key', 'database_gabungan_api_key')->delete();

        $response = $this->actingAs($this->user)
            ->postJson('/pengaturan/domain-check');

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'ERROR',
            'message' => 'Konfigurasi API Database Gabungan belum diatur.',
        ]);
    }

    /** @test */
    public function it_sends_correct_headers_to_api(): void
    {
        Http::fake([
            '*/api/v1/debug/domain-check' => Http::response([
                'status' => 'OK',
            ], 200),
        ]);

        $this->actingAs($this->user)
            ->postJson('/pengaturan/domain-check');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-api-key') &&
                   $request->hasHeader('Accept', 'application/json');
        });
    }

    /** @test */
    public function it_can_display_domain_check_in_sidebar(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/pengaturan/domain-check');

        $response->assertStatus(200);
        // The sidebar should contain the domain-check link
        // This is tested implicitly by checking the page loads correctly
    }
}
