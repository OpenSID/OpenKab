<?php

namespace App\Console\Commands;

use App\Models\Sso\OpenKabSsoToken;
use Illuminate\Console\Command;

class SsoPurgeTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:purge-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus token SSO yang kedaluwarsa atau sudah digunakan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = OpenKabSsoToken::query()
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                    ->orWhereNotNull('used_at');
            })
            ->delete();

        $this->info("Pembersihan selesai. {$deleted} token SSO dihapus.");

        return 0;
    }
}
