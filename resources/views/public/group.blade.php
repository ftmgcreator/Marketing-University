@extends('layouts.public')
@include('public._helpers')

@section('title', $group->name.' — Guruh statistikasi')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('public.dashboard') }}">Bosh sahifa</a>
        @if ($group->department->faculty)
            <span class="sep">/</span>
            <a href="{{ route('public.faculty', $group->department->faculty->slug) }}">{{ $group->department->faculty->name }}</a>
        @endif
        <span class="sep">/</span>
        <a href="{{ route('public.department', $group->department->slug) }}">{{ $group->department->name }}</a>
        @if ($group->curator)
            <span class="sep">/</span>
            <a href="{{ route('public.curator', $group->curator->slug) }}">{{ $group->curator->full_name }}</a>
        @endif
        <span class="sep">/</span>
        <span>{{ $group->name }}</span>
    </div>

    <div class="page-head">
        <h1 class="page-title">{{ $group->name }}</h1>
        <p class="page-subtitle">
            {{ $group->speciality_name }}
            @if ($group->curator) · Kurator: <a href="{{ route('public.curator', $group->curator->slug) }}">{{ $group->curator->full_name }}</a>@endif
        </p>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="label">Talabalar</div>
            <div class="value">{{ $group->student_count }}</div>
            <div class="sub">jami guruhda</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="label">To'lagan</div>
            <div class="value">{{ $group->paid_count }}</div>
            <div class="sub money">{{ fmt_money($group->paid_amount) }} so'm</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="label">Qarzdor</div>
            <div class="value">{{ $group->debt_count }}</div>
            <div class="sub money">{{ fmt_money($group->debt_amount) }} so'm</div>
        </div>
        <div class="stat-card neutral">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="label">Bajarilish</div>
            <div class="value">{{ $group->percent_paid }}%</div>
            <div class="sub">shartnoma summasidan</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Talabalar ro'yxati
            </h2>
            <div class="filter-tabs">
                <a href="{{ route('public.group', $group->slug) }}" class="{{ $filter === 'all' ? 'active' : '' }}">Hammasi · {{ $group->student_count }}</a>
                <a href="{{ route('public.group', ['slug' => $group->slug, 'filter' => 'debtors']) }}" class="{{ $filter === 'debtors' ? 'active' : '' }}">Qarzdorlar · {{ $group->debt_count }}</a>
                <a href="{{ route('public.group', ['slug' => $group->slug, 'filter' => 'paid']) }}" class="{{ $filter === 'paid' ? 'active' : '' }}">To'laganlar · {{ $group->paid_count }}</a>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th class="left">F.I.Sh</th>
                        <th>Kurs</th>
                        <th class="num">Shartnoma</th>
                        <th class="num">To'langan</th>
                        <th class="num">Qarz</th>
                        <th>Holat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $i => $s)
                        <tr>
                            <td class="idx"><span class="rank {{ rank_class($i) }}">{{ $i + 1 }}</span></td>
                            <td class="left">
                                <span class="name-cell">
                                    <span class="avatar">{{ initials($s->full_name) }}</span>
                                    <span>{{ $s->full_name }}</span>
                                </span>
                            </td>
                            <td><span class="badge neutral">{{ $s->course }}</span></td>
                            <td class="num money">{{ fmt_money($s->contract_amount) }}</td>
                            <td class="num money" style="color:#34d399;">{{ fmt_money($s->paid_amount) }}</td>
                            <td class="num money">
                                @if ($s->debt_amount > 0)
                                    <span style="color:#f87171;">{{ fmt_money($s->debt_amount) }}</span>
                                @else
                                    <span style="color:var(--text-dim);">0</span>
                                @endif
                            </td>
                            <td>
                                @if ($s->is_debtor)
                                    <span class="badge danger">Qarzdor</span>
                                @else
                                    <span class="badge success">To'lagan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">Bu filtr bo'yicha talaba topilmadi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
