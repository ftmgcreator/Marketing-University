<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SpecialityImporter;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessSpecialityImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $filePath,
        public string $originalName,
        public ?int $userId = null,
    ) {}

    public function handle(SpecialityImporter $importer): void
    {
        $absolutePath = Storage::disk('local')->path($this->filePath);

        try {
            $stats = $importer->import($absolutePath);

            $this->notifyUser(true, sprintf(
                '%s — %d ta noyob mutaxassislik (%d qator).',
                $this->originalName,
                $stats['unique_rows'],
                $stats['total_rows'],
            ));
        } catch (\Throwable $e) {
            $this->notifyUser(false, $this->originalName.' — '.$e->getMessage());
            throw $e;
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->notifyUser(false, $this->originalName.' — '.$exception->getMessage());

        if ($this->filePath) {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    private function notifyUser(bool $success, string $message): void
    {
        if (! $this->userId) {
            return;
        }

        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        $notification = Notification::make()
            ->title($success ? 'Mutaxassisliklar import qilindi' : 'Mutaxassisliklarni import qilishda xatolik')
            ->body($message);

        $success ? $notification->success() : $notification->danger();

        $notification->sendToDatabase($user);
    }
}
