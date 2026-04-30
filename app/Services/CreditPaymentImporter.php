<?php

namespace App\Services;

use App\Models\CreditContract;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CreditPaymentImporter
{
    public function import(string $filePath): array
    {
        ini_set('memory_limit', '2048M');

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        $rows = [];
        $totalRows = 0;
        $skippedRows = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $jshshir = $this->normalizeJshshir($sheet->getCell("A{$r}")->getValue());
            $paid = $this->toInt($sheet->getCell("B{$r}")->getValue());

            if ($jshshir === null && $paid === null) {
                continue;
            }

            $totalRows++;

            if ($jshshir === null || $paid === null) {
                $skippedRows++;
                continue;
            }

            $rows[] = ['jshshir' => $jshshir, 'paid' => $paid, 'row' => $r];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $updated = 0;
        $notFound = 0;

        DB::transaction(function () use ($rows, &$updated, &$notFound): void {
            foreach ($rows as $entry) {
                $contract = CreditContract::where('jshshir', $entry['jshshir'])
                    ->orderByDesc('contract_date')
                    ->orderByDesc('id')
                    ->first();

                if (! $contract) {
                    $notFound++;
                    continue;
                }

                $paid = $entry['paid'];
                $total = (int) $contract->total_amount;
                $status = match (true) {
                    $paid <= 0 => 'pending',
                    $paid >= $total => 'paid',
                    default => 'partial',
                };

                $contract->update([
                    'paid_amount' => $paid,
                    'payment_status' => $status,
                ]);

                $updated++;
            }
        });

        return [
            'total_rows' => $totalRows,
            'updated' => $updated,
            'not_found' => $notFound,
            'skipped' => $skippedRows,
        ];
    }

    private function normalizeJshshir(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            $value = number_format((float) $value, 0, '.', '');
        }

        $clean = preg_replace('/\D/', '', (string) $value);

        return $clean === '' ? null : $clean;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        return $clean === '' ? null : (int) $clean;
    }
}
