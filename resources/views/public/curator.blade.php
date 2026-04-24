@extends('layouts.public')
@include('public._helpers')

@section('title', $curator->full_name.' — Kurator statistikasi')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('public.dashboard') }}">Bosh sahifa</a>
        @if ($curator->department->faculty)
            <span class="sep">/</span>
            <a href="{{ route('public.faculty', $curator->department->faculty->slug) }}">{{ $curator->department->faculty->name }}</a>
        @endif
        <span class="sep">/</span>
        <a href="{{ route('public.department', $curator->department->slug) }}">{{ $curator->department->name }}</a>
        <span class="sep">/</span>
        <span>{{ $curator->full_name }}</span>
    </div>

    <div class="page-head">
        <h1 class="page-title">{{ $curator->full_name }}</h1>
        <p class="page-subtitle">{{ $curator->department->name }} — kurator</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div class="label">Guruhlar</div>
            <div class="value">{{ $curator->group_count }}</div>
            <div class="sub">{{ $curator->student_count }} ta talaba</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="label">To'langan</div>
            <div class="value money">{{ fmt_money($curator->paid_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge success">{{ $curator->paid_count }} talaba</span></div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="label">Qoldiq</div>
            <div class="value money">{{ fmt_money($curator->debt_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge danger">{{ $curator->debt_count }} qarzdor</span></div>
        </div>
        <div class="stat-card neutral">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="label">Bajarilish</div>
            <div class="value">{{ $curator->percent_paid }}%</div>
            <div class="sub">shartnoma summasidan</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Guruhlar
            </h2>
            <span class="meta">{{ $groups->count() }} ta guruh</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th class="left">Guruh nomi</th>
                        <th class="left">Mutaxassislik</th>
                        <th class="num">Talaba</th>
                        <th class="num">Qarzdor</th>
                        <th class="num">Qoldiq</th>
                        <th>Bajarilish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $i => $g)
                        <tr>
                            <td class="idx"><span class="rank {{ rank_class($i) }}">{{ $i + 1 }}</span></td>
                            <td class="left">
                                <a class="row-link" href="{{ route('public.group', $g->slug) }}">{{ $g->name }}</a>
                            </td>
                            <td class="left muted">{{ $g->speciality_name }}</td>
                            <td class="num"><span class="badge neutral">{{ $g->student_count }}</span></td>
                            <td class="num"><span class="badge danger">{{ $g->debt_count }}</span></td>
                            <td class="num money">
                                @if ($g->debt_amount > 0)
                                    <span style="color:#f87171;">{{ fmt_money($g->debt_amount) }}</span>
                                @else
                                    <span style="color:var(--text-dim);">0</span>
                                @endif
                            </td>
                            <td>{!! ring_progress((float) $g->percent_paid) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
