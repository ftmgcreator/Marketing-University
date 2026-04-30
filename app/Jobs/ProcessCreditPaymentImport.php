<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CreditPaymentImporter;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessCreditPaymentImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $filePath,
        public string $originalName,
        public ?int $userId = null,
    ) {}

    public function handle(CreditPaymentImporter $importer): void
    {
        $absolutePath = Storage::disk('local')->path($this->filePath);

        try {
            $stats = $importer->import($absolutePath);

            $this->notifyUser(true, sprintf(
                "%s — %d ta yangilandi, %d ta topilmadi, %d ta o'tkazildi (%d qator).",
                $this->originalName,
                $stats['updated'],
                $stats['not_found'],
                $stats['skipped'],
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
            ->title($success ? "To'lovlar import qilindi" : "To'lovlarni import qilishda xatolik")
            ->body($message);

        $success ? $notification->success() : $notification->danger();

        $notification->sendToDatabase($user);
    }
}
