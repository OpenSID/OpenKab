<?php

namespace Tests\Feature;

use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\WebsiteTestCase;

class WebsiteHomePageTest extends WebsiteTestCase
{
    #[Test]
    public function it_opens_website_home_page_when_website_enabled_and_default_page_selected()
    {
        $response = $this->get(route('web.index'));

        $response->assertStatus(200);
        $response->assertViewIs('web.index');
        $response->assertViewHas('categoriesItems');
        $response->assertViewHas('listKabupaten');
        $response->assertViewHas('listKecamatan');
        $response->assertViewHas('listDesa');
    }

    #[Test]
    public function it_redirects_to_login_when_website_is_disabled()
    {
        Setting::updateOrCreate(
            ['key' => 'website_enable'],
            ['value' => 0]
        );

        $response = $this->get(route('web.index'));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function it_redirects_to_presisi_when_home_page_is_set_to_presisi()
    {
        Setting::updateOrCreate(
            ['key' => 'home_page'],
            ['value' => 'presisi']
        );

        $response = $this->get(route('web.index'));

        $response->assertRedirect('presisi');
    }
}
