<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupQrTokenUsages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:cleanup-token-usages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup QR token usages older than 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = \Illuminate\Support\Facades\DB::table('qr_token_usages')
            ->where('used_at', '<', now()->subDay())
            ->delete();

        $this->info("Deleted {$deleted} QR token usages older than 1 day.");
    }
}
