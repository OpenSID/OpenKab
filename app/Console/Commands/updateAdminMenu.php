<?php

namespace App\Console\Commands;

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Console\Command;

class updateAdminMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:menu-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update default menu administrator';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $team = Team::whereName('administrator')->first();
        if ($team) {
            $team->menu = Modul::Menu;
            $team->save();
        }

        return Command::SUCCESS;
    }
}
