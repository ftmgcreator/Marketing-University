<?php

namespace App\Services;

use App\Models\FacultyConfig;
use App\Models\Report;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramReporter
{
    private const API = 'https://api.telegram.org/bot';

    public function __construct(
        private readonly FacultyExcelGenerator $excelGenerator,
    ) {}

    /**
     * @return array{sent: int, skipped: int, failed: int, errors: array<string>}
     */
    public function sendReport(Report $report): array
    {
        $token = config('services.telegram.bot_token');
        if (empty($token)) {
            throw new \RuntimeException('Telegram bot token sozlanmagan (.env: TELEGRAM_BOT_TOKEN)');
        }

        $stats = ['sent' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];

        $configs = FacultyConfig::where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->get()
            ->keyBy('name');

        foreach ($report->faculties as $faculty) {
            $config = $configs->get($faculty->name);
            if (! $config) {
                $stats['skipped']++;
                continue;
            }

            try {
                $excelPath = $this->excelGenerator->generate($faculty);
                $this->sendToFaculty($token, $config->telegram_chat_id, $faculty, $excelPath, $report);
                @unlink($excelPath);
                $stats['sent']++;
            } catch (\Throwable $e) {
                $stats['failed']++;
                $stats['errors'][] = $faculty->name.': '.$e->getMessage();
                Log::error('Telegram report failed for '.$faculty->name, [
                    'exception' => $e,
                ]);
            }
        }

        return $stats;
    }

    private function sendToFaculty(string $token, string $chatId, $faculty, string $excelPath, Report $report): void
    {
        $caption = $this->buildCaption($faculty, $report);

        $response = Http::timeout(60)
            ->attach('document', file_get_contents($excelPath), basename($excelPath))
            ->post(self::API.$token.'/sendDocument', [
                'chat_id' => $chatId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

        if (! $response->successful() || ($response->json('ok') !== true)) {
            $description = $response->json('description') ?? $response->body();
            throw new \RuntimeException('Telegram API: '.$description);
        }
    }

    private function buildCaption($faculty, Report $report): string
    {
        $url = url('/fakultet/'.$faculty->slug);
        $date = $report->report_date->format('d.m.Y');

        $contractAmount = number_format((float) $faculty->contract_amount, 0, '.', ' ');
        $paidAmount = number_format((float) $faculty->paid_amount, 0, '.', ' ');
        $debtAmount = number_format((float) $faculty->debt_amount, 0, '.', ' ');
        $studentCount = number_format($faculty->student_count, 0, '.', ' ');

        return implode("\n", [
            "<b>📊 Termiz IqSU — To'lov hisoboti</b>",
            "📅 <b>Sana:</b> {$date}",
            "🏛 <b>Fakultet:</b> {$faculty->name}",
            "",
            "👥 <b>Talabalar:</b> {$studentCount}",
            "✅ <b>To'lagan:</b> {$faculty->paid_count}",
            "❌ <b>Qarzdor:</b> {$faculty->debt_count}",
            "",
            "💰 <b>Shartnoma:</b> {$contractAmount} so'm",
            "✔️ <b>To'langan:</b> {$paidAmount} so'm",
            "⚠️ <b>Qoldiq:</b> {$debtAmount} so'm",
            "",
            "📈 <b>Bajarilish:</b> {$faculty->percent_paid}%",
            "",
            "🔗 <a href=\"{$url}\">Batafsil sahifa</a>",
        ]);
    }
}
