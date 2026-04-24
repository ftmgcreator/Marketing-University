@extends('layouts.public')
@include('public._helpers')

@section('title', 'Umumiy statistika — TISU Marketing')

@section('content')
    <div class="page-head">
        <h1 class="page-title">Umumiy statistika</h1>
        <p class="page-subtitle">Termiz iqtisodiyot va servis universiteti — kafedralar kesimida shartnoma to'lovlari va qarzdorlik tahlili</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="label">Talabalar</div>
            <div class="value">{{ number_format($totals['student_count'], 0, '.', ' ') }}</div>
            <div class="sub">
                <span class="badge success">{{ number_format($totals['paid_count'], 0, '.', ' ') }} to'lagan</span>
                <span class="badge danger">{{ number_format($totals['debt_count'], 0, '.', ' ') }} qarzdor</span>
            </div>
        </div>

        <div class="stat-card neutral">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="label">Shartnoma summasi</div>
            <div class="value money">{{ fmt_money($totals['contract_amount']) }}<span class="currency">so'm</span></div>
            <div class="sub">Umumiy reja</div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="label">To'langan summa</div>
            <div class="value money">{{ fmt_money($totals['paid_amount']) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge success">{{ $totals['percent_paid'] }}% bajarilgan</span></div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="label">Qoldiq qarzdorlik</div>
            <div class="value money">{{ fmt_money($totals['debt_amount']) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge danger">{{ number_format($totals['debt_count'], 0, '.', ' ') }} qarzdor</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                Fakultetlar kesimi
            </h2>
            <span class="meta">{{ $faculties->count() }} ta fakultet</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th class="left">Fakultet nomi</th>
                        <th class="num">Kafedra</th>
                        <th class="num">Talaba</th>
                        <th class="num">Shartnoma</th>
                        <th class="num">To'langan</th>
                        <th class="num">Qoldiq</th>
                        <th>Bajarilish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($faculties as $i => $f)
                        <tr>
                            <td class="idx"><span class="rank {{ rank_class($i) }}">{{ $i + 1 }}</span></td>
                            <td class="left">
                                <span class="name-cell">
                                    <span class="avatar">{{ initials($f->name) }}</span>
                                    <a class="row-link" href="{{ route('public.faculty', $f->slug) }}">{{ $f->name }}</a>
                                </span>
                            </td>
                            <td class="num"><span class="badge primary">{{ $f->department_count }}</span></td>
                            <td class="num"><span class="badge neutral">{{ number_format($f->student_count, 0, '.', ' ') }}</span></td>
                            <td class="num money">{{ fmt_money($f->contract_amount) }}</td>
                            <td class="num money" style="color:#34d399;">{{ fmt_money($f->paid_amount) }}</td>
                            <td class="num money">
                                @if ($f->debt_amount > 0)
                                    <span style="color:#f87171;">{{ fmt_money($f->debt_amount) }}</span>
                                @else
                                    <span style="color:var(--text-dim);">0</span>
                                @endif
                            </td>
                            <td>{!! ring_progress((float) $f->percent_paid) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
