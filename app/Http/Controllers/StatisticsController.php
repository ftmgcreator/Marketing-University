<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Report;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function dashboard()
    {
        $report = $this->activeReport();

        if (! $report) {
            return view('public.empty');
        }

        $faculties = $report->faculties()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->get();

        $totals = [
            'student_count' => $faculties->sum('student_count'),
            'paid_count' => $faculties->sum('paid_count'),
            'debt_count' => $faculties->sum('debt_count'),
            'contract_amount' => $faculties->sum('contract_amount'),
            'paid_amount' => $faculties->sum('paid_amount'),
            'debt_amount' => $faculties->sum('debt_amount'),
        ];
        $totals['percent_paid'] = $totals['contract_amount'] > 0
            ? round(($totals['paid_amount'] / $totals['contract_amount']) * 100, 2)
            : 0;

        return view('public.dashboard', compact('report', 'faculties', 'totals'));
    }

    public function faculty(string $slug)
    {
        $report = $this->activeReport();
        abort_unless($report, 404);

        $faculty = $report->faculties()->where('slug', $slug)->firstOrFail();

        $departments = $faculty->departments()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->get();

        return view('public.faculty', compact('report', 'faculty', 'departments'));
    }

    public function department(string $slug)
    {
        $report = $this->activeReport();
        abort_unless($report, 404);

        $department = $report->departments()
            ->with('faculty:id,name,slug')
            ->where('slug', $slug)
            ->firstOrFail();

        $curators = $department->curators()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->get();

        return view('public.department', compact('report', 'department', 'curators'));
    }

    public function curator(string $slug)
    {
        $report = $this->activeReport();
        abort_unless($report, 404);

        $curator = $report->curators()
            ->with(['department:id,name,slug,faculty_id', 'department.faculty:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        $groups = $curator->groups()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->get();

        return view('public.curator', compact('report', 'curator', 'groups'));
    }

    public function group(Request $request, string $slug)
    {
        $report = $this->activeReport();
        abort_unless($report, 404);

        $group = $report->groups()
            ->with([
                'department:id,name,slug,faculty_id',
                'department.faculty:id,name,slug',
                'curator:id,full_name,slug',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $filter = $request->get('filter', 'all');

        $studentsQuery = $group->students()
            ->orderByDesc('percent_paid')
            ->orderByDesc('paid_amount')
            ->orderBy('full_name');

        if ($filter === 'debtors') {
            $studentsQuery->where('is_debtor', true);
        } elseif ($filter === 'paid') {
            $studentsQuery->where('is_debtor', false);
        }

        $students = $studentsQuery->get();

        return view('public.group', compact('report', 'group', 'students', 'filter'));
    }

    private function activeReport(): ?Report
    {
        return Report::where('is_active', true)
            ->orderByDesc('report_date')
            ->first();
    }
}
