<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\User;
use App\Services\ReportImporter;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessReportImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(public int $importJobId) {}

    public function handle(ReportImporter $importer): void
    {
        $job = ImportJob::find($this->importJobId);
        if (! $job) {
            return;
        }

        $job->update([
            'status' => ImportJob::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $absolutePath = Storage::disk('local')->path($job->file_path);

        try {
            $report = $importer->import(
                $absolutePath,
                $job->original_name,
                new \DateTimeImmutable($job->report_date->toDateString())
            );

            $job->update([
                'status' => ImportJob::STATUS_COMPLETED,
                'report_id' => $report->id,
                'finished_at' => now(),
                'message' => sprintf(
                    '%d kafedra, %d guruh, %d talaba',
                    $report->departments()->count(),
                    $report->groups()->count(),
                    $report->students()->count(),
                ),
            ]);

            $this->notifyUser($job, true);
        } catch (\Throwable $e) {
            $job->update([
                'status' => ImportJob::STATUS_FAILED,
                'finished_at' => now(),
                'message' => $e->getMessage(),
            ]);

            $this->notifyUser($job, false);

            throw $e;
        } finally {
            Storage::disk('local')->delete($job->file_path);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $job = ImportJob::find($this->importJobId);
        if (! $job) {
            return;
        }

        if ($job->status !== ImportJob::STATUS_FAILED) {
            $job->update([
                'status' => ImportJob::STATUS_FAILED,
                'finished_at' => now(),
                'message' => $exception->getMessage(),
            ]);

            $this->notifyUser($job, false);
        }

        if ($job->file_path) {
            Storage::disk('local')->delete($job->file_path);
        }
    }

    private function notifyUser(ImportJob $job, bool $success): void
    {
        if (! $job->user_id) {
            return;
        }

        $user = User::find($job->user_id);
        if (! $user) {
            return;
        }

        if ($success) {
            Notification::make()
                ->title('Hisobot muvaffaqiyatli yuklandi')
                ->body($job->original_name.' — '.$job->message)
                ->success()
                ->sendToDatabase($user);
        } else {
            Notification::make()
                ->title('Hisobotni yuklashda xatolik')
                ->body($job->original_name.' — '.$job->message)
                ->danger()
                ->sendToDatabase($user);
        }
    }
}
