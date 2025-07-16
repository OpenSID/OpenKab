<?php

namespace Tests\Feature;

use App\Models\Enums\StatistikPendudukEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BaseTestCase;

class DasborDemografiControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_demografi_index()
    {
        $response = $this->get(route('dasbor-demografi'));

        $response->assertStatus(200);
        $response->assertViewIs('demografi.index');
        $response->assertViewHas('chartItems');
        $response->assertViewHas('statistikUrl');
    }

    /** @test */
    public function it_returns_correct_statistik_url()
    {
        $response = $this->get(route('dasbor-demografi'));

        $response->assertViewHas('statistikUrl', 'api/v1/statistik/penduduk');
    }

    /** @test */
    public function it_returns_correct_chart_items_structure()
    {
        $response = $this->get(route('dasbor-demografi'));

        $response->assertViewHas('chartItems', function ($chartItems) {
            return is_array($chartItems) &&
                count($chartItems) === 9 &&
                collect($chartItems)->every(function ($item) {
                    return isset($item['url_detail'], $item['key'], $item['text']);
                });
        });
    }

    /** @test */
    public function it_has_correct_chart_item_keys()
    {
        $response = $this->get(route('dasbor-demografi'));

        $expectedKeys = [
            StatistikPendudukEnum::RENTANG_UMUR['slug'],
            StatistikPendudukEnum::STATUS_PERKAWINAN['slug'],
            StatistikPendudukEnum::PENDIDIKAN_KK['slug'],
            StatistikPendudukEnum::GOLONGAN_DARAH['slug'],
            StatistikPendudukEnum::PENYAKIT_MENAHUN['slug'],
            StatistikPendudukEnum::AGAMA['slug'],
            StatistikPendudukEnum::JENIS_KELAMIN['slug'],
            'suku',
            StatistikPendudukEnum::PENYANDANG_CACAT['slug'],
        ];

        $response->assertViewHas('chartItems', function ($chartItems) use ($expectedKeys) {
            $keys = collect($chartItems)->pluck('key')->toArray();

            return $keys === $expectedKeys;
        });
    }

    /** @test */
    public function it_has_correct_chart_item_labels()
    {
        $response = $this->get(route('dasbor-demografi'));

        $expectedLabels = [
            StatistikPendudukEnum::RENTANG_UMUR['label'],
            StatistikPendudukEnum::STATUS_PERKAWINAN['label'],
            StatistikPendudukEnum::PENDIDIKAN_KK['label'],
            StatistikPendudukEnum::GOLONGAN_DARAH['label'],
            StatistikPendudukEnum::PENYAKIT_MENAHUN['label'],
            StatistikPendudukEnum::AGAMA['label'],
            StatistikPendudukEnum::JENIS_KELAMIN['label'],
            StatistikPendudukEnum::SUKU_ETNIS['label'],
            StatistikPendudukEnum::PENYANDANG_CACAT['label'],
        ];

        $response->assertViewHas('chartItems', function ($chartItems) use ($expectedLabels) {
            $labels = collect($chartItems)->pluck('text')->toArray();

            return $labels === $expectedLabels;
        });
    }

    /** @test */
    public function it_has_correct_detail_urls_in_chart_items()
    {
        $response = $this->get(route('dasbor-demografi'));

        $expectedDetailUrl = url('statistik/penduduk');

        $response->assertViewHas('chartItems', function ($chartItems) use ($expectedDetailUrl) {
            return collect($chartItems)->every(function ($item) use ($expectedDetailUrl) {
                return $item['url_detail'] === $expectedDetailUrl;
            });
        });
    }
}
