<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class CheckPresisiStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah status di model Pengaturan adalah 'presisi'
        $status = Setting::where('key', 'home_page')->first()->value; // Sesuaikan logika pengambilan datanya
        
        if ($status !== 'presisi') {
            // Jika status bukan 'presisi', arahkan pengguna ke halaman lain atau tampilkan pesan error
            return redirect('/');
        }

        return $next($request);
    }
}

