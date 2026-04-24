<?php

namespace App\Services;

use App\Models\Curator;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Report;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportImporter
{
    private const GROUPS_SHEET = 'Guruhlar';
    private const STUDENTS_SHEET = "Shartnoma to'lovlari ro'yxati";

    public function import(string $filePath, string $originalName, ?\DateTimeInterface $reportDate = null): Report
    {
        ini_set('memory_limit', '2048M');

        $reportDate ??= now();

        return DB::transaction(function () use ($filePath, $originalName, $reportDate) {
            $report = Report::create([
                'report_date' => $reportDate,
                'file_name' => $originalName,
                'is_active' => false,
            ]);

            $groupMeta = $this->parseGroupsSheet($filePath);
            $studentRows = $this->parseStudentsSheet($filePath);
            $groupStats = $this->aggregateGroupStats($studentRows);
            $studentFacultyByGroup = $this->dominantFacultyPerGroup($studentRows);

            [$deptMap, $curatorMap, $groupMap] = $this->persistHierarchy($report, $groupMeta, $groupStats);

            $this->persistStudents($report, $studentRows, $groupMap);
            unset($studentRows);

            $this->recomputeAggregates($deptMap, $curatorMap);
            $this->assignFaculties($report, $deptMap, $studentFacultyByGroup);

            Report::where('id', '!=', $report->id)->update(['is_active' => false]);
            $report->update(['is_active' => true]);

            return $report;
        });
    }

    private function parseGroupsSheet(string $filePath): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setLoadSheetsOnly([self::GROUPS_SHEET]);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheetByName(self::GROUPS_SHEET);
        $highestRow = $sheet->getHighestDataRow();

        $rows = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $groupName = trim((string) $sheet->getCell("D{$r}")->getValue());
            if ($groupName === '') {
                continue;
            }

            $rows[] = [
                'curator' => trim((string) $sheet->getCell("B{$r}")->getValue()),
                'department' => trim((string) $sheet->getCell("C{$r}")->getValue()),
                'group' => $groupName,
                'speciality_code' => trim((string) $sheet->getCell("K{$r}")->getValue()),
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows;
    }

    private function parseStudentsSheet(string $filePath): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setLoadSheetsOnly([self::STUDENTS_SHEET]);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheetByName(self::STUDENTS_SHEET);
        $highestRow = $sheet->getHighestDataRow();

        $rows = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $fullName = trim((string) $sheet->getCell("B{$r}")->getValue());
            if ($fullName === '') {
                continue;
            }

            $contractAmount = $this->toNumber($sheet->getCell("K{$r}")->getValue());
            $paidAmount = $this->toNumber($sheet->getCell("O{$r}")->getValue());
            $debtAmount = $this->toNumber($sheet->getCell("P{$r}")->getValue());

            $rows[] = [
                'full_name' => $fullName,
                'group_name' => trim((string) $sheet->getCell("C{$r}")->getValue()),
                'faculty' => trim((string) $sheet->getCell("D{$r}")->getValue()),
                'speciality' => trim((string) $sheet->getCell("E{$r}")->getValue()),
                'course' => trim((string) $sheet->getCell("F{$r}")->getValue()),
                'education_form' => trim((string) $sheet->getCell("G{$r}")->getValue()),
                'contract_type' => trim((string) $sheet->getCell("I{$r}")->getValue()),
                'previous_year_amount' => $this->toNumber($sheet->getCell("J{$r}")->getValue()),
                'contract_amount' => $contractAmount,
                'paid_amount' => $paidAmount,
                'debt_amount' => $debtAmount,
                'percent_paid' => $this->percent($paidAmount, $contractAmount),
                'is_debtor' => $debtAmount > 0,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows;
    }

    private function aggregateGroupStats(array $studentRows): array
    {
        $stats = [];
        foreach ($studentRows as $s) {
            $key = mb_strtolower($s['group_name']);
            if (!isset($stats[$key])) {
                $stats[$key] = [
                    'faculty' => $s['faculty'],
                    'speciality_name' => $s['speciality'],
                    'student_count' => 0,
                    'paid_count' => 0,
                    'debt_count' => 0,
                    'contract_amount' => 0.0,
                    'paid_amount' => 0.0,
                    'debt_amount' => 0.0,
                ];
            }
            $stats[$key]['student_count']++;
            $stats[$key]['contract_amount'] += $s['contract_amount'];
            $stats[$key]['paid_amount'] += $s['paid_amount'];
            $stats[$key]['debt_amount'] += $s['debt_amount'];
            if ($s['is_debtor']) {
                $stats[$key]['debt_count']++;
            } else {
                $stats[$key]['paid_count']++;
            }
        }
        return $stats;
    }

    private function persistHierarchy(Report $report, array $groupMeta, array $groupStats): array
    {
        $deptMap = [];
        $curatorMap = [];
        $groupMap = [];
        $usedDeptSlugs = [];
        $usedCuratorSlugs = [];
        $usedGroupSlugs = [];

        foreach ($groupMeta as $row) {
            $deptName = $row['department'] ?: 'Noma\'lum kafedra';
            $deptKey = mb_strtolower($deptName);

            if (!isset($deptMap[$deptKey])) {
                $slug = $this->uniqueSlug($deptName, $usedDeptSlugs);
                $usedDeptSlugs[] = $slug;
                $deptMap[$deptKey] = Department::create([
                    'report_id' => $report->id,
                    'name' => $deptName,
                    'slug' => $slug,
                ]);
            }
            $department = $deptMap[$deptKey];

            $curatorName = $row['curator'] ?: 'Biriktirilmagan';
            $curatorKey = $deptKey.'|'.mb_strtolower($curatorName);

            if (!isset($curatorMap[$curatorKey])) {
                $slug = $this->uniqueSlug($curatorName.'-'.$deptName, $usedCuratorSlugs);
                $usedCuratorSlugs[] = $slug;
                $curatorMap[$curatorKey] = Curator::create([
                    'report_id' => $report->id,
                    'department_id' => $department->id,
                    'full_name' => $curatorName,
                    'slug' => $slug,
                ]);
            }
            $curator = $curatorMap[$curatorKey];

            $groupKey = mb_strtolower($row['group']);
            $stats = $groupStats[$groupKey] ?? [
                'faculty' => null, 'speciality_name' => null,
                'student_count' => 0, 'paid_count' => 0, 'debt_count' => 0,
                'contract_amount' => 0, 'paid_amount' => 0, 'debt_amount' => 0,
            ];

            $groupSlug = $this->uniqueSlug($row['group'], $usedGroupSlugs);
            $usedGroupSlugs[] = $groupSlug;

            $group = Group::create([
                'report_id' => $report->id,
                'department_id' => $department->id,
                'curator_id' => $curator->id,
                'name' => $row['group'],
                'slug' => $groupSlug,
                'faculty' => $stats['faculty'] ?: null,
                'speciality_code' => $row['speciality_code'] ?: null,
                'speciality_name' => $stats['speciality_name'] ?: null,
                'student_count' => $stats['student_count'],
                'paid_count' => $stats['paid_count'],
                'debt_count' => $stats['debt_count'],
                'contract_amount' => $stats['contract_amount'],
                'paid_amount' => $stats['paid_amount'],
                'debt_amount' => $stats['debt_amount'],
                'percent_paid' => $this->percent($stats['paid_amount'], $stats['contract_amount']),
            ]);

            $groupMap[$groupKey] = $group;
        }

        return [$deptMap, $curatorMap, $groupMap];
    }

    private function persistStudents(Report $report, array $studentRows, array $groupMap): void
    {
        $buffer = [];
        $chunkSize = 500;
        $now = now();

        foreach ($studentRows as $s) {
            $groupId = $groupMap[mb_strtolower($s['group_name'])]->id ?? null;

            $buffer[] = [
                'report_id' => $report->id,
                'group_id' => $groupId,
                'full_name' => $s['full_name'],
                'group_name' => $s['group_name'] ?: null,
                'faculty' => $s['faculty'] ?: null,
                'speciality' => $s['speciality'] ?: null,
                'course' => $s['course'] ?: null,
                'education_form' => $s['education_form'] ?: null,
                'contract_type' => $s['contract_type'] ?: null,
                'previous_year_amount' => $s['previous_year_amount'],
                'contract_amount' => $s['contract_amount'],
                'paid_amount' => $s['paid_amount'],
                'debt_amount' => $s['debt_amount'],
                'percent_paid' => $s['percent_paid'],
                'is_debtor' => $s['is_debtor'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($buffer) >= $chunkSize) {
                Student::insert($buffer);
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            Student::insert($buffer);
        }
    }

    private function recomputeAggregates(array $deptMap, array $curatorMap): void
    {
        foreach ($curatorMap as $curator) {
            $agg = Group::where('curator_id', $curator->id)
                ->selectRaw('COUNT(*) as gc, SUM(student_count) as s, SUM(paid_count) as p, SUM(debt_count) as d, SUM(contract_amount) as ca, SUM(paid_amount) as pa, SUM(debt_amount) as da')
                ->first();

            $curator->update([
                'group_count' => (int) $agg->gc,
                'student_count' => (int) $agg->s,
                'paid_count' => (int) $agg->p,
                'debt_count' => (int) $agg->d,
                'contract_amount' => (float) $agg->ca,
                'paid_amount' => (float) $agg->pa,
                'debt_amount' => (float) $agg->da,
                'percent_paid' => $this->percent((float) $agg->pa, (float) $agg->ca),
            ]);
        }

        foreach ($deptMap as $department) {
            $agg = Group::where('department_id', $department->id)
                ->selectRaw('SUM(student_count) as s, SUM(paid_count) as p, SUM(debt_count) as d, SUM(contract_amount) as ca, SUM(paid_amount) as pa, SUM(debt_amount) as da')
                ->first();

            $department->update([
                'student_count' => (int) $agg->s,
                'paid_count' => (int) $agg->p,
                'debt_count' => (int) $agg->d,
                'contract_amount' => (float) $agg->ca,
                'paid_amount' => (float) $agg->pa,
                'debt_amount' => (float) $agg->da,
                'percent_paid' => $this->percent((float) $agg->pa, (float) $agg->ca),
            ]);
        }
    }

    private function dominantFacultyPerGroup(array $studentRows): array
    {
        $byGroup = [];
        foreach ($studentRows as $s) {
            $g = mb_strtolower($s['group_name']);
            $f = trim($s['faculty']);
            if ($f === '') continue;
            $byGroup[$g][$f] = ($byGroup[$g][$f] ?? 0) + 1;
        }
        $result = [];
        foreach ($byGroup as $g => $counts) {
            arsort($counts);
            $result[$g] = array_key_first($counts);
        }
        return $result;
    }

    private function assignFaculties(Report $report, array $deptMap, array $groupFacultyMap): void
    {
        $facultyMap = [];
        $usedSlugs = [];

        foreach ($deptMap as $department) {
            $facultyVotes = [];
            $department->loadMissing('groups');
            foreach ($department->groups as $g) {
                $f = $groupFacultyMap[mb_strtolower($g->name)] ?? null;
                if (! $f) continue;
                $weight = max(1, $g->student_count);
                $facultyVotes[$f] = ($facultyVotes[$f] ?? 0) + $weight;
            }

            if (empty($facultyVotes)) {
                continue;
            }

            arsort($facultyVotes);
            $facultyName = array_key_first($facultyVotes);
            $facultyKey = mb_strtolower($facultyName);

            if (! isset($facultyMap[$facultyKey])) {
                $slug = $this->uniqueSlug($facultyName, $usedSlugs);
                $usedSlugs[] = $slug;
                $facultyMap[$facultyKey] = Faculty::create([
                    'report_id' => $report->id,
                    'name' => $facultyName,
                    'slug' => $slug,
                ]);
            }

            $department->update(['faculty_id' => $facultyMap[$facultyKey]->id]);
        }

        foreach ($facultyMap as $faculty) {
            $deptAgg = Department::where('faculty_id', $faculty->id)
                ->selectRaw('COUNT(*) as dc, SUM(student_count) as s, SUM(paid_count) as p, SUM(debt_count) as d, SUM(contract_amount) as ca, SUM(paid_amount) as pa, SUM(debt_amount) as da')
                ->first();

            $curatorCount = Curator::whereIn('department_id', Department::where('faculty_id', $faculty->id)->pluck('id'))->count();
            $groupCount = Group::whereIn('department_id', Department::where('faculty_id', $faculty->id)->pluck('id'))->count();

            $faculty->update([
                'department_count' => (int) $deptAgg->dc,
                'curator_count' => $curatorCount,
                'group_count' => $groupCount,
                'student_count' => (int) $deptAgg->s,
                'paid_count' => (int) $deptAgg->p,
                'debt_count' => (int) $deptAgg->d,
                'contract_amount' => (float) $deptAgg->ca,
                'paid_amount' => (float) $deptAgg->pa,
                'debt_amount' => (float) $deptAgg->da,
                'percent_paid' => $this->percent((float) $deptAgg->pa, (float) $deptAgg->ca),
            ]);
        }
    }

    private function toNumber(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $clean = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : 0;
    }

    private function percent(float $paid, float $total): float
    {
        return $total > 0 ? round(($paid / $total) * 100, 2) : 0;
    }

    private function uniqueSlug(string $source, array $existing): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'n-'.Str::random(6);
        }
        $slug = $base;
        $i = 2;
        while (in_array($slug, $existing, true)) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
