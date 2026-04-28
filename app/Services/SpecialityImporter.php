<?php

namespace App\Services;

use App\Models\Speciality;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpecialityImporter
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

        $deduped = [];
        $totalRows = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $name = trim((string) $sheet->getCell("D{$r}")->getValue());
            if ($name === '') {
                continue;
            }

            $totalRows++;

            $type = trim((string) $sheet->getCell("A{$r}")->getValue());
            $faculty = trim((string) $sheet->getCell("B{$r}")->getValue());
            $code = trim((string) $sheet->getCell("C{$r}")->getValue());
            $form = trim((string) $sheet->getCell("E{$r}")->getValue());
            $amount = $this->toInt($sheet->getCell("F{$r}")->getValue());

            $key = $code.'|'.$form.'|'.$type;
            $deduped[$key] = [
                'education_type' => $type !== '' ? $type : null,
                'faculty' => $faculty !== '' ? $faculty : null,
                'code' => $code !== '' ? $code : null,
                'name' => $name,
                'education_form' => $form !== '' ? $form : null,
                'contract_amount' => $amount,
                'is_active' => true,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $now = now();
        $rows = array_map(static function (array $row) use ($now) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            return $row;
        }, array_values($deduped));

        $inserted = 0;
        DB::transaction(function () use ($rows, &$inserted) {
            foreach (array_chunk($rows, 500) as $chunk) {
                Speciality::upsert(
                    $chunk,
                    ['code', 'education_form', 'education_type'],
                    ['faculty', 'name', 'contract_amount', 'is_active', 'updated_at']
                );
                $inserted += count($chunk);
            }
        });

        return [
            'total_rows' => $totalRows,
            'unique_rows' => count($rows),
            'inserted' => $inserted,
        ];
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
