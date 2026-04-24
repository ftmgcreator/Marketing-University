<?php

namespace App\Services;

use App\Models\Faculty;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FacultyExcelGenerator
{
    public function generate(Faculty $faculty): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->addSummarySheet($spreadsheet, $faculty);
        $this->addDepartmentsSheet($spreadsheet, $faculty);
        $this->addCuratorsSheet($spreadsheet, $faculty);
        $this->addGroupsSheet($spreadsheet, $faculty);
        $this->addDebtorsSheet($spreadsheet, $faculty);

        $spreadsheet->setActiveSheetIndex(0);

        $tmpDir = storage_path('app/private/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $reportDate = $faculty->report->report_date->format('Y-m-d');
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $faculty->slug);
        $path = $tmpDir.DIRECTORY_SEPARATOR.$reportDate.'_'.$safeName.'.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $path;
    }

    private function addSummarySheet(Spreadsheet $sp, Faculty $f): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Umumiy');

        $sheet->setCellValue('A1', $f->name.' fakulteti — to\'lov hisoboti');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Sana:');
        $sheet->setCellValue('B2', $f->report->report_date->format('d.m.Y'));

        $rows = [
            ['Kafedralar soni',  $f->department_count],
            ['Kuratorlar soni',  $f->curator_count],
            ['Guruhlar soni',    $f->group_count],
            ['Talabalar soni',   $f->student_count],
            ['To\'lagan',        $f->paid_count],
            ['Qarzdor',          $f->debt_count],
            ['Shartnoma summa',  (float) $f->contract_amount],
            ['To\'langan summa', (float) $f->paid_amount],
            ['Qoldiq qarz',      (float) $f->debt_amount],
            ['Bajarilish foizi', $f->percent_paid.'%'],
        ];

        $r = 4;
        foreach ($rows as [$label, $val]) {
            $sheet->setCellValue("A{$r}", $label);
            $sheet->setCellValue("B{$r}", $val);
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            if (is_float($val)) {
                $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $r++;
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(28);
    }

    private function addDepartmentsSheet(Spreadsheet $sp, Faculty $f): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Kafedralar');

        $headers = ['#', 'Kafedra', 'Talaba', 'To\'lagan', 'Qarzdor', 'Shartnoma', 'To\'langan', 'Qoldiq', 'Foiz'];
        $this->writeHeader($sheet, $headers);

        $rows = $f->departments()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->get();

        $r = 2;
        foreach ($rows as $i => $d) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $d->name);
            $sheet->setCellValue("C{$r}", $d->student_count);
            $sheet->setCellValue("D{$r}", $d->paid_count);
            $sheet->setCellValue("E{$r}", $d->debt_count);
            $sheet->setCellValue("F{$r}", (float) $d->contract_amount);
            $sheet->setCellValue("G{$r}", (float) $d->paid_amount);
            $sheet->setCellValue("H{$r}", (float) $d->debt_amount);
            $sheet->setCellValue("I{$r}", $d->percent_paid / 100);
            $sheet->getStyle("F{$r}:H{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $r++;
        }

        $this->autoSizeColumns($sheet, range('A', 'I'));
        $this->applyBorders($sheet, "A1:I".($r - 1));
    }

    private function addCuratorsSheet(Spreadsheet $sp, Faculty $f): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Kuratorlar');

        $headers = ['#', 'Kafedra', 'F.I.Sh', 'Guruh', 'Talaba', 'To\'lagan', 'Qarzdor', 'Shartnoma', 'To\'langan', 'Qoldiq', 'Foiz'];
        $this->writeHeader($sheet, $headers);

        $rows = $f->curators()
            ->with('department:id,name')
            ->orderByDesc('curators.percent_paid')
            ->orderByDesc('curators.paid_amount')
            ->get();

        $r = 2;
        foreach ($rows as $i => $c) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $c->department->name ?? '');
            $sheet->setCellValue("C{$r}", $c->full_name);
            $sheet->setCellValue("D{$r}", $c->group_count);
            $sheet->setCellValue("E{$r}", $c->student_count);
            $sheet->setCellValue("F{$r}", $c->paid_count);
            $sheet->setCellValue("G{$r}", $c->debt_count);
            $sheet->setCellValue("H{$r}", (float) $c->contract_amount);
            $sheet->setCellValue("I{$r}", (float) $c->paid_amount);
            $sheet->setCellValue("J{$r}", (float) $c->debt_amount);
            $sheet->setCellValue("K{$r}", $c->percent_paid / 100);
            $sheet->getStyle("H{$r}:J{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $r++;
        }

        $this->autoSizeColumns($sheet, range('A', 'K'));
        $this->applyBorders($sheet, "A1:K".($r - 1));
    }

    private function addGroupsSheet(Spreadsheet $sp, Faculty $f): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Guruhlar');

        $headers = ['#', 'Kafedra', 'Kurator', 'Guruh', 'Talaba', 'Qarzdor', 'Shartnoma', 'To\'langan', 'Qoldiq', 'Foiz'];
        $this->writeHeader($sheet, $headers);

        $rows = $f->groups()
            ->with(['department:id,name', 'curator:id,full_name'])
            ->orderByDesc('groups.percent_paid')
            ->orderByDesc('groups.paid_amount')
            ->get();

        $r = 2;
        foreach ($rows as $i => $g) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $g->department->name ?? '');
            $sheet->setCellValue("C{$r}", $g->curator->full_name ?? '');
            $sheet->setCellValue("D{$r}", $g->name);
            $sheet->setCellValue("E{$r}", $g->student_count);
            $sheet->setCellValue("F{$r}", $g->debt_count);
            $sheet->setCellValue("G{$r}", (float) $g->contract_amount);
            $sheet->setCellValue("H{$r}", (float) $g->paid_amount);
            $sheet->setCellValue("I{$r}", (float) $g->debt_amount);
            $sheet->setCellValue("J{$r}", $g->percent_paid / 100);
            $sheet->getStyle("G{$r}:I{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $r++;
        }

        $this->autoSizeColumns($sheet, range('A', 'J'));
        $this->applyBorders($sheet, "A1:J".($r - 1));
    }

    private function addDebtorsSheet(Spreadsheet $sp, Faculty $f): void
    {
        $sheet = $sp->createSheet();
        $sheet->setTitle('Qarzdorlar');

        $headers = ['#', 'Kafedra', 'Kurator', 'Guruh', 'Talaba F.I.Sh', 'Kurs', 'Shartnoma', 'To\'langan', 'Qarz'];
        $this->writeHeader($sheet, $headers);

        $deptIds = $f->departments()->pluck('id');
        $groupIds = \App\Models\Group::whereIn('department_id', $deptIds)->pluck('id');

        $debtors = \App\Models\Student::whereIn('group_id', $groupIds)
            ->where('is_debtor', true)
            ->where('report_id', $f->report_id)
            ->with(['group.department:id,name', 'group.curator:id,full_name'])
            ->orderBy('group_name')
            ->orderByDesc('debt_amount')
            ->get();

        $r = 2;
        foreach ($debtors as $i => $s) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $s->group->department->name ?? '');
            $sheet->setCellValue("C{$r}", $s->group->curator->full_name ?? '');
            $sheet->setCellValue("D{$r}", $s->group_name);
            $sheet->setCellValue("E{$r}", $s->full_name);
            $sheet->setCellValue("F{$r}", $s->course);
            $sheet->setCellValue("G{$r}", (float) $s->contract_amount);
            $sheet->setCellValue("H{$r}", (float) $s->paid_amount);
            $sheet->setCellValue("I{$r}", (float) $s->debt_amount);
            $sheet->getStyle("G{$r}:I{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $r++;
        }

        $this->autoSizeColumns($sheet, range('A', 'I'));
        $this->applyBorders($sheet, "A1:I".($r - 1));
    }

    private function writeHeader($sheet, array $headers): void
    {
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col.'1', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle("A1:{$lastCol}1")
            ->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastCol}1")
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle("A1:{$lastCol}1")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A2');
    }

    private function autoSizeColumns($sheet, array $cols): void
    {
        foreach ($cols as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
    }

    private function applyBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('E5E7EB');
    }
}
