<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Speciality;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AdmissionImporter
{
    /**
     * Excel ustun-tartibi (Qabul varag'i):
     * A=№, B=F.I.SH, C=JSHSHIR, D=Pasport, E=Telefon, F=Mut.kodi, G=Mut.nomi,
     * H=Ta'lim turi, I=Qabul statusi, J=Ta'lim shakli, K=Kursi, L=Yashash hududi,
     * M=Tel 2, N=Shartnoma summasi, O=IZOH, P=Fakultet, Q=Sana
     */
    public function import(string $filePath): array
    {
        ini_set('memory_limit', '2048M');

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);

        $sheet = $spreadsheet->getSheetByName('Qabul') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        $rows = [];
        $totalRows = 0;
        $skippedRows = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $fullName = $this->toString($sheet->getCell("B{$r}")->getValue());
            $jshshir = $this->normalizeJshshir($sheet->getCell("C{$r}")->getValue());

            if ($fullName === null && $jshshir === null) {
                continue;
            }

            $totalRows++;

            if ($fullName === null) {
                $skippedRows++;
                continue;
            }

            $rows[] = [
                'row' => $r,
                'full_name' => $fullName,
                'jshshir' => $jshshir,
                'passport' => $this->toString($sheet->getCell("D{$r}")->getValue()),
                'phone' => $this->normalizePhone($sheet->getCell("E{$r}")->getValue()),
                'speciality_code' => $this->toString($sheet->getCell("F{$r}")->getValue()),
                'speciality_name' => $this->toString($sheet->getCell("G{$r}")->getValue()),
                'education_type' => $this->toString($sheet->getCell("H{$r}")->getValue()),
                'status_raw' => $this->toString($sheet->getCell("I{$r}")->getValue()),
                'education_form' => $this->toString($sheet->getCell("J{$r}")->getValue()),
                'course' => $this->toString($sheet->getCell("K{$r}")->getValue()),
                'region' => $this->toString($sheet->getCell("L{$r}")->getValue()),
                'phone2' => $this->normalizePhone($sheet->getCell("M{$r}")->getValue()),
                'contract_amount' => $this->toAmount($sheet->getCell("N{$r}")->getValue()),
                'notes' => $this->toString($sheet->getCell("O{$r}")->getValue()),
                'faculty' => $this->toString($sheet->getCell("P{$r}")->getValue()),
                'admission_date' => $this->toDate($sheet->getCell("Q{$r}")->getValue()),
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, &$created, &$updated): void {
            foreach ($rows as $entry) {
                $speciality = $this->resolveSpeciality($entry['speciality_code'], $entry['speciality_name']);

                $contractAmount = ($speciality && (int) $speciality->contract_amount > 0)
                    ? (float) $speciality->contract_amount
                    : ($entry['contract_amount'] ?? 0);

                $payload = [
                    'full_name' => $entry['full_name'],
                    'jshshir' => $entry['jshshir'],
                    'passport' => $entry['passport'],
                    'phone' => $entry['phone'],
                    'phone2' => $entry['phone2'],
                    'region' => $entry['region'],
                    'speciality_id' => $speciality?->id,
                    'speciality_code' => $entry['speciality_code'],
                    'faculty' => $entry['faculty'],
                    'education_type' => $entry['education_type'],
                    'education_form' => $entry['education_form'],
                    'course' => $entry['course'],
                    'contract_amount' => $contractAmount,
                    'admission_date' => $entry['admission_date'],
                    'status' => $this->mapStatus($entry['status_raw']),
                    'notes' => $entry['notes'],
                ];

                $existing = $entry['jshshir']
                    ? Admission::where('jshshir', $entry['jshshir'])->first()
                    : null;

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Admission::create($payload);
                    $created++;
                }
            }
        });

        return [
            'total_rows' => $totalRows,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skippedRows,
        ];
    }

    private function resolveSpeciality(?string $code, ?string $name): ?Speciality
    {
        if ($code) {
            $clean = trim($code);
            $found = Speciality::where('code', $clean)->first();
            if ($found) {
                return $found;
            }
        }

        if ($name) {
            $clean = trim($name);
            $found = Speciality::where('name', $clean)->first();
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function mapStatus(?string $raw): string
    {
        if (! $raw) {
            return Admission::STATUS_PENDING;
        }

        $normalized = mb_strtolower(trim($raw));

        return match (true) {
            str_contains($normalized, 'qabul') => Admission::STATUS_APPROVED,
            str_contains($normalized, 'rad') => Admission::STATUS_REJECTED,
            default => Admission::STATUS_PENDING,
        };
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

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            $value = number_format((float) $value, 0, '.', '');
        }

        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private function toString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }

    private function toAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return $clean === '' ? null : (float) $clean;
    }

    private function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
