<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdentitasControllerApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_put_perbarui()
    {
        $user = \App\Models\User::inRandomOrder()->first();
        Sanctum::actingAs($user);
        $identitas = \App\Models\Identitas::inRandomOrder()->first();
        $url = '/api/v1/identitas/perbarui/'.$identitas->id.'?'.http_build_query([

        ]);        
        $dataUpdate = [
            'nama_aplikasi' => 'Kabupaten Baru',
            'nama_kabupaten' => 'Simanis',
        ];
        $identitas->nama_aplikasi = $dataUpdate['nama_aplikasi'];
        $identitas->nama_kabupaten = $dataUpdate['nama_kabupaten'];
        $response = $this->putJson($url, $identitas->toArray());
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('identitas', $dataUpdate);
    }

    public function test_post_upload()
    {
        $user = \App\Models\User::inRandomOrder()->first();
        Sanctum::actingAs($user);
        $identitas = \App\Models\Identitas::inRandomOrder()->first();
        $url = '/api/v1/identitas/upload/'.$identitas->id.'?'.http_build_query([

        ]);        
        $dataUpdate = [
            'file' => UploadedFile::fake()->image('avatar.png'),
        ];
        
        $response = $this->postJson($url, $dataUpdate);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
        $fileName = $response->json('data');        
        $logoFileName = explode('/',$fileName);
        $dataLogo = [
            'id' => $identitas->id,
            'logo' => end($logoFileName),
        ];
        Storage::disk('public')->assertExists('img/'.$dataLogo['logo']);
        $this->assertDatabaseHas('identitas', $dataLogo);
    }

    public function test_post_upload_favicon()
    {
        $user = \App\Models\User::inRandomOrder()->first();
        Sanctum::actingAs($user);
        $identitas = \App\Models\Identitas::inRandomOrder()->first();
        $url = '/api/v1/identitas/uploadFavicon/'.$identitas->id.'?'.http_build_query([

        ]);        
        $dataUpdate = [
            'file' => UploadedFile::fake()->image('favicon.png'),
        ];
        
        $response = $this->postJson($url, $dataUpdate);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
        $fileName = $response->json('data');      
        echo $fileName;  
        $logoFileName = explode('/',$fileName);
        $dataLogo = [
            'id' => $identitas->id,
            'favicon' => end($logoFileName),
        ];
        
        $this->assertTrue(File::exists('public/favicons/favicon-96x96.png'));
        $this->assertDatabaseHas('identitas', $dataLogo);
    }
}
