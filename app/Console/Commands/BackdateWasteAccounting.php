<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\ProductWaste;
use App\Services\AccountingService;
use Illuminate\Console\Command;

class BackdateWasteAccounting extends Command
{
    protected $signature = 'waste:backdate-accounting';
    protected $description = 'Post journal entries for existing waste records that do not have accounting entries';

    public function handle(AccountingService $accounting): int
    {
        $existingIds = JournalEntry::where('reference_type', 'ProductWaste')
            ->pluck('reference_id')
            ->toArray();

        $wastes = ProductWaste::whereNotIn('id', $existingIds)->get();

        if ($wastes->isEmpty()) {
            $this->info('No waste records need backdating.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($wastes->count());
        $bar->start();

        foreach ($wastes as $waste) {
            try {
                $accounting->postWaste(
                    $waste->id,
                    (float) $waste->total_cost,
                    $waste->waste_date->toDateString()
                );
            } catch (\Throwable $e) {
                $this->error("Failed for waste ID {$waste->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$wastes->count()} waste records.");

        return self::SUCCESS;
    }
}
