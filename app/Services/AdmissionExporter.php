<?php

namespace App\Services;

use App\Models\Admission;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AdmissionExporter
{
    /**
     * Excel ustun-tartibi (Qabul varag'i) — import bilan bir xil:
     * A=№, B=F.I.SH, C=JSHSHIR, D=Pasport, E=Telefon, F=Mut.kodi, G=Mut.nomi,
     * H=Ta'lim turi, I=Qabul statusi, J=Ta'lim shakli, K=Kursi, L=Yashash hududi,
     * M=Tel 2, N=Shartnoma summasi, O=IZOH, P=Fakultet, Q=Sana
     */
    private const HEADERS = [
        'A' => '№',
        'B' => 'F.I.SH',
        'C' => 'JSHSHIR',
        'D' => 'Pasport',
        'E' => 'Telefon',
        'F' => 'Mut.kodi',
        'G' => 'Mut.nomi',
        'H' => "Ta'lim turi",
        'I' => 'Qabul statusi',
        'J' => "Ta'lim shakli",
        'K' => 'Kursi',
        'L' => 'Yashash hududi',
        'M' => 'Tel 2',
        'N' => 'Shartnoma summasi',
        'O' => 'IZOH',
        'P' => 'Fakultet',
        'Q' => 'Sana',
    ];

    public function exportAll(): string
    {
        ini_set('memory_limit', '2048M');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Qabul');

        $this->writeHeaders($sheet);

        $row = 2;
        $index = 1;

        Admission::query()
            ->with('speciality')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->chunk(500, function ($admissions) use ($sheet, &$row, &$index): void {
                foreach ($admissions as $a) {
                    $sheet->setCellValue("A{$row}", $index++);
                    $sheet->setCellValue("B{$row}", $a->full_name);
                    $sheet->setCellValueExplicit("C{$row}", (string) ($a->jshshir ?? ''), DataType::TYPE_STRING);
                    $sheet->setCellValue("D{$row}", $a->passport);
                    $sheet->setCellValueExplicit("E{$row}", (string) ($a->phone ?? ''), DataType::TYPE_STRING);
                    $sheet->setCellValue("F{$row}", $a->speciality_code);
                    $sheet->setCellValue("G{$row}", $a->speciality?->name);
                    $sheet->setCellValue("H{$row}", $a->education_type);
                    $sheet->setCellValue("I{$row}", Admission::STATUSES[$a->status] ?? $a->status);
                    $sheet->setCellValue("J{$row}", $a->education_form);
                    $sheet->setCellValue("K{$row}", $a->course);
                    $sheet->setCellValue("L{$row}", $a->region);
                    $sheet->setCellValueExplicit("M{$row}", (string) ($a->phone2 ?? ''), DataType::TYPE_STRING);
                    $sheet->setCellValue("N{$row}", (int) $a->contract_amount);
                    $sheet->setCellValue("O{$row}", $a->notes);
                    $sheet->setCellValue("P{$row}", $a->faculty);
                    $sheet->setCellValue("Q{$row}", $a->admission_date?->format('d.m.Y'));

                    $row++;
                }
            });

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("N2:N{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A1:Q{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $this->autosize($sheet);
        $sheet->freezePane('A2');

        return $this->save($spreadsheet, 'talabalar_'.now()->format('Y-m-d_His').'.xlsx');
    }

    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Qabul');

        $this->writeHeaders($sheet);

        $sheet->getStyle('C:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('M:M')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('N:N')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('Q:Q')->getNumberFormat()->setFormatCode('dd.mm.yyyy');

        $sample = [
            'A' => 1,
            'B' => 'Jumanazarova Zarina Rashid qizi',
            'C' => '12345678901234',
            'D' => 'AC2275207',
            'E' => '887237303',
            'F' => '60230100-3',
            'G' => 'Ona tili va adabiyoti',
            'H' => 'Bakalavr',
            'I' => 'Kutilmoqda',
            'J' => 'Kunduzgi',
            'K' => '1 kurs',
            'L' => "Qumqo'rg'on tumani",
            'M' => '887237304',
            'N' => 12000000,
            'O' => "4 000 000 to'ladi. 02.07.2025 sanada.",
            'P' => 'Pedagogika',
            'Q' => now()->format('d.m.Y'),
        ];

        foreach ($sample as $col => $value) {
            if (in_array($col, ['C', 'E', 'M'], true)) {
                $sheet->setCellValueExplicit("{$col}2", (string) $value, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue("{$col}2", $value);
            }
        }

        $sheet->getStyle('A2:Q2')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');
        $sheet->getStyle('A1:Q2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->autosize($sheet);
        $sheet->freezePane('A2');

        return $this->save($spreadsheet, 'talabalar_shabloni.xlsx');
    }

    private function writeHeaders($sheet): void
    {
        foreach (self::HEADERS as $col => $label) {
            $sheet->setCellValue("{$col}1", $label);
        }

        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Q1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E7FF');
        $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function autosize($sheet): void
    {
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function save(Spreadsheet $spreadsheet, string $fileName): string
    {
        $tmpDir = storage_path('app/private/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $path = $tmpDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $path;
    }
}
