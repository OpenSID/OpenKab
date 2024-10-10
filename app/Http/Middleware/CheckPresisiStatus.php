<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CheckPresisiStatus
{
    /**
     * Handle an incoming request.
     *
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
