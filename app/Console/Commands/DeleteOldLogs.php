<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class DeleteOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "logs:delete-old";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Menghapus file log yang sudah lebih dari 2 minggu.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = 14;
        $logPath = storage_path("logs");
        $cutoff = Carbon::now()->subDays($days);
        $deleted = 0;
        $files = File::files($logPath);
        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp($file->getMTime());
            if ($lastModified->lt($cutoff)) {
                File::delete($file->getPathname());
                $this->info("Deleted: {$file->getFilename()}");
                $deleted++;
            }
        }
        $this->info("Total deleted  {$deleted} file(s)");
        return Command::SUCCESS;
    }
}
