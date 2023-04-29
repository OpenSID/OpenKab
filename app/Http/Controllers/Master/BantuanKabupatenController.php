<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\BantuanRequest;
use GuzzleHttp\Exception\ClientException;

class BantuanKabupatenController extends Controller
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => "http://127.0.0.1:8000/api/v1/bantuan-kabupaten/",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('master.bantuan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.bantuan.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BantuanRequest $request)
    {
        // $data = $request->all();
        // dd($request->nama);
        try {

            $this->client->post('buat', [
                // 'headers' => [
                //     'Accept'        => 'application/json',
                //     'Authorization' => "Bearer {$this->setting->api_opendk_key}",
                // ],
                'multipart' => [
                    ['name' => 'nama', 'contents' => $request->nama],
                    ['name' => 'sasaran', 'contents' => $request->sasaran],
                    ['name' => 'ndesc', 'contents' => $request->ndesc],
                    ['name' => 'sdate', 'contents' => $request->sdate],
                ],
            ]);

            return redirect()->route('bantuan.index')->with('success', 'Bantuan berhasil ditambahkan!');
        } catch (ClientException $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
