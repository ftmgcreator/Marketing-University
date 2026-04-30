<?php

namespace App\Services;

use App\Models\CreditContract;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CreditContractExporter
{
    public function exportAll(): string
    {
        ini_set('memory_limit', '2048M');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Shartnomalar');

        $headers = [
            '№',
            'Shartnoma raqami',
            'Sana',
            'F.I.Sh.',
            'JSHSHR',
            'Telefon',
            'Fakultet',
            'Mutaxassislik',
            "Ta'lim shakli",
            'Kurs',
            'Guruh',
            'Kreditlar soni',
            'Umumiy summa',
            "To'langan",
            'Qarz',
            "To'lov holati",
        ];

        foreach ($headers as $i => $header) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue("{$col}1", $header);
        }

        $headerRange = 'A1:'.chr(ord('A') + count($headers) - 1).'1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E7FF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $statusLabels = [
            'pending' => 'Kutilmoqda',
            'partial' => "Qisman to'langan",
            'paid' => "To'langan",
        ];

        $row = 2;
        $index = 1;

        CreditContract::query()
            ->orderByDesc('contract_date')
            ->orderByDesc('id')
            ->chunk(500, function ($contracts) use ($sheet, &$row, &$index, $statusLabels): void {
                foreach ($contracts as $c) {
                    $total = (int) $c->total_amount;
                    $paid = (int) $c->paid_amount;
                    $debt = max(0, $total - $paid);

                    $sheet->setCellValue("A{$row}", $index++);
                    $sheet->setCellValue("B{$row}", $c->contract_number);
                    $sheet->setCellValue("C{$row}", $c->contract_date?->format('d.m.Y'));
                    $sheet->setCellValue("D{$row}", $c->full_name);
                    $sheet->setCellValueExplicit("E{$row}", (string) $c->jshshir, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValue("F{$row}", $c->phone);
                    $sheet->setCellValue("G{$row}", $c->faculty);
                    $sheet->setCellValue("H{$row}", $c->speciality);
                    $sheet->setCellValue("I{$row}", $c->education_form);
                    $sheet->setCellValue("J{$row}", $c->course);
                    $sheet->setCellValue("K{$row}", $c->group_name);
                    $sheet->setCellValue("L{$row}", (int) $c->credits_count);
                    $sheet->setCellValue("M{$row}", $total);
                    $sheet->setCellValue("N{$row}", $paid);
                    $sheet->setCellValue("O{$row}", $debt);
                    $sheet->setCellValue("P{$row}", $statusLabels[$c->payment_status] ?? $c->payment_status);

                    $row++;
                }
            });

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $moneyRange = "M2:O{$lastRow}";
            $sheet->getStyle($moneyRange)->getNumberFormat()->setFormatCode('#,##0');

            $tableRange = "A1:P{$lastRow}";
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $tmpDir = storage_path('app/private/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $path = $tmpDir.DIRECTORY_SEPARATOR.'shartnomalar_'.now()->format('Y-m-d_His').'.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $path;
    }

    public function generatePaymentTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("To'lov shabloni");

        $sheet->setCellValue('A1', 'JSHSHR');
        $sheet->setCellValue('B1', "To'langan summa");

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E7FF');
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);

        $tmpDir = storage_path('app/private/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $path = $tmpDir.DIRECTORY_SEPARATOR.'tolov_shabloni.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $path;
    }
}
