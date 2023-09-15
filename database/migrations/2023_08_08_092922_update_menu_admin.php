<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Team::where(['name' => 'administrator'])->update(['menu' => '[{"text": "Kependudukan","icon": "fa fa-users","role": "kependudukan","submenu": [{"icon": "fas fa-angle-right","text": "Penduduk","url": "penduduk","role": "penduduk","selected": true}],"selected": true}, {"text": "Statistik","icon": "fas fa-chart-pie","role": "statistik","submenu": [{"icon": "fas fa-angle-right","text": "Penduduk","url": "statistik\/penduduk","role": "statistik-penduduk","selected": true}, {"icon": "fas fa-angle-right","text": "Keluarga","url": "statistik\/keluarga","role": "statistik-keluarga","selected": true}, {"icon": "fas fa-angle-right","text": "RTM","url": "statistik\/rtm","role": "statistik-rtm","selected": true}, {"icon": "fas fa-angle-right","text": "Bantuan","url": "statistik\/bantuan","role": "statistik-bantuan","selected": true}],"selected": true}, {"text": "Bantuan","icon": "fas fa-handshake","url": "bantuan","role": "bantuan","selected": true}, {"text": "Master Data","icon": "fa fa-tags","role": "master-data","submenu": [{"icon": "fas fa-angle-right","text": "Bantuan","url": "master\/bantuan","role": "master-data-bantuan","selected": true}, {"icon": "fas fa-angle-right","text": "Kategori Artikel","url": "master\/kategori\/0","role": "master-data-artikel","selected": true}, {"icon": "fas fa-angle-right","text": "Pengaturan Aplikasi","url": "master\/pengaturan","role": "master-data-pengaturan","selected": true}],"selected": true}, {"text": "Pengaturan","icon": "fa fa-cog","role": "pengaturan","submenu": [{"icon": "fas fa-angle-right","text": "Identitas","url": "pengaturan\/identitas","role": "pengaturan-identitas","selected": true}, {"icon": "fas fa-angle-right","text": "Pengguna","url": "pengaturan\/users","role": "pengaturan-users","selected": true}, {"icon": "fas fa-angle-right","text": "Group","url": "pengaturan\/groups","role": "pengaturan-group","selected": true}, {"icon": "fas fa-angle-right","text": "Riwayat Pengguna","url": "pengaturan\/activities","role": "pengaturan-users","selected": true}],"selected": true}]']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Team::where(['name' => 'administrator'])->update(['menu' => '[{"text":"Kependudukan","icon":"fa fa-users","role":"kependudukan","submenu":[{"icon":"fas fa-angle-right","text":"Penduduk","url":"penduduk","role":"penduduk","selected":true}],"selected":true},{"text":"Statistik","icon":"fas fa-chart-pie","role":"statistik","submenu":[{"icon":"fas fa-angle-right","text":"Penduduk","url":"statistik\/penduduk","role":"statistik-penduduk","selected":true},{"icon":"fas fa-angle-right","text":"Keluarga","url":"statistik\/keluarga","role":"statistik-keluarga","selected":true},{"icon":"fas fa-angle-right","text":"RTM","url":"statistik\/rtm","role":"statistik-rtm","selected":true},{"icon":"fas fa-angle-right","text":"Bantuan","url":"statistik\/bantuan","role":"statistik-bantuan","selected":true}],"selected":true},{"text":"Bantuan","icon":"fas fa-handshake","url":"bantuan","role":"bantuan","selected":true},{"text":"Master Data","icon":"fa fa-tags","role":"master-data","submenu":[{"icon":"fas fa-angle-right","text":"Bantuan","url":"master\/bantuan","role":"master-data-bantuan","selected":true},{"icon":"fas fa-angle-right","text":"Kategori Artikel","url":"master\/kategori\/0","role":"master-data-artikel","selected":true},{"icon":"fas fa-angle-right","text":"Pengaturan Aplikasi","url":"master\/pengaturan","role":"master-data-pengaturan","selected":true}],"selected":true},{"text":"Pengaturan","icon":"fa fa-cog","role":"pengaturan","submenu":[{"icon":"fas fa-angle-right","text":"Identitas","url":"pengaturan\/identitas","role":"pengaturan-identitas","selected":true},{"icon":"fas fa-angle-right","text":"Pengguna","url":"pengaturan\/users","role":"pengaturan-users","selected":true},{"icon":"fas fa-angle-right","text":"Group","url":"pengaturan\/groups","role":"pengaturan-group","selected":true}],"selected":true}]']);
    }
};
