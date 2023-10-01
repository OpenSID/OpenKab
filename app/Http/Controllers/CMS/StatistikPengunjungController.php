<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Akaunting\Apexcharts\Chart;
use Illuminate\Support\Facades\DB;
use Shetabit\Visitor\Models\Visit;

class StatistikPengunjungController extends AppBaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $visitorDevice = Visit::selectRaw('device, count(device) as total')->groupBy('device')->get();
        $visitorDaily = Visit::select(
                DB::raw("count(*) as hit_total"),
                DB::raw("count(distinct ip) as unique_visitor"),
                DB::raw("(DATE_FORMAT(created_at, '%d-%m-%Y')) as tanggal")
            )->groupBy(DB::raw("(DATE_FORMAT(created_at, '%d-%m-%Y'))"))
            ->get();
        $visitorPost = Visit::select(
                DB::raw("count(url) as total"),
                'url'
            )->groupBy('url')
            ->get();
        $chartDeviceVisitor = (new Chart)->setType('donut')
            ->setWidth('100%')
            ->setTitle('Jumlah kunjungan berdasarkan gawai')
            ->setSubtitle('Prosentase')
            ->setHeight(300)
            ->setLabels($visitorDevice->pluck('device')->toArray())
            ->setDataLabelsEnabled(true)
            ->setDataset('Pengunjung berdasarkan gawai', 'donut', $visitorDevice->pluck('total')->toArray());
        $chartVisitorDaily = (new Chart)->setType('bar')
            ->setWidth('100%')
            ->setTitle('Jumlah kunjungan harian')
            ->setSubtitle('Jumlah')
            ->setHeight(300)
            ->setStacked(true)
            ->setLabels($visitorDaily->pluck('tanggal')->toArray())
            ->setDataLabelsEnabled(true)
            ->setSeries([
                ['name' => 'Pengunjung unik', 'data' => $visitorDaily->pluck('unique_visitor')->toArray()],
                ['name' => 'Kunjungan','data' => $visitorDaily->pluck('hit_total')->toArray()]
            ]);
        $chartVisitorPost = (new Chart)->setType('bar')
            ->setWidth('100%')
            ->setSubtitle('Jumlah')
            ->setTitle('Jumlah kunjungan berdasarkan url')
            ->setHeight(300)
            ->setLabels($visitorPost->pluck('url')->toArray())
            ->setDataLabelsEnabled(true)
            ->setSeries([
                ['name' => 'Pengunjung unik', 'data' => $visitorPost->pluck('total')->toArray()],
            ]);
        return view('statistik_pengunjung.index', compact('chartDeviceVisitor', 'chartVisitorDaily', 'chartVisitorPost'));
    }
}
