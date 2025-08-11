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
        return $next($request);
        $website = Setting::where(['key' => 'website_enable'])->first()?->value ?? 0;

        if (! $website) {
            return redirect('login');
        }
        // Cek apakah status di model Pengaturan adalah 'presisi'
        $homePage = Setting::where('key', 'home_page')->first()->value; // Sesuaikan logika pengambilan datanya

        if ($homePage !== 'presisi') {
            // Jika status bukan 'presisi', arahkan pengguna ke halaman lain atau tampilkan pesan error
            return redirect('/');
        }

        return $next($request);
    }
}
