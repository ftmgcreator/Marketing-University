@extends('layouts.public')
@include('public._helpers')

@section('title', $department->name.' — Kafedra statistikasi')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('public.dashboard') }}">Bosh sahifa</a>
        @if ($department->faculty)
            <span class="sep">/</span>
            <a href="{{ route('public.faculty', $department->faculty->slug) }}">{{ $department->faculty->name }}</a>
        @endif
        <span class="sep">/</span>
        <span>{{ $department->name }}</span>
    </div>

    <div class="page-head">
        <h1 class="page-title">{{ $department->name }}</h1>
        <p class="page-subtitle">Kafedra bo'yicha kuratorlar va guruhlar statistikasi</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="label">Talabalar</div>
            <div class="value">{{ number_format($department->student_count, 0, '.', ' ') }}</div>
            <div class="sub">
                <span class="badge success">{{ $department->paid_count }} to'lagan</span>
                <span class="badge danger">{{ $department->debt_count }} qarzdor</span>
            </div>
        </div>
        <div class="stat-card neutral">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="label">Shartnoma</div>
            <div class="value money">{{ fmt_money($department->contract_amount) }}<span class="currency">so'm</span></div>
            <div class="sub">Umumiy reja</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="label">To'langan</div>
            <div class="value money">{{ fmt_money($department->paid_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge success">{{ $department->percent_paid }}% bajarilgan</span></div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="label">Qoldiq</div>
            <div class="value money">{{ fmt_money($department->debt_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge danger">{{ $department->debt_count }} qarzdor</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Kuratorlar
            </h2>
            <span class="meta">{{ $curators->count() }} ta kurator</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th class="left">F.I.Sh</th>
                        <th class="num">Guruh</th>
                        <th class="num">Talaba</th>
                        <th class="num">Qarzdor</th>
                        <th class="num">Qoldiq</th>
                        <th>Bajarilish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curators as $i => $c)
                        <tr>
                            <td class="idx"><span class="rank {{ rank_class($i) }}">{{ $i + 1 }}</span></td>
                            <td class="left">
                                <span class="name-cell">
                                    <span class="avatar">{{ initials($c->full_name) }}</span>
                                    <a class="row-link" href="{{ route('public.curator', $c->slug) }}">{{ $c->full_name }}</a>
                                </span>
                            </td>
                            <td class="num"><span class="badge primary">{{ $c->group_count }}</span></td>
                            <td class="num"><span class="badge neutral">{{ $c->student_count }}</span></td>
                            <td class="num"><span class="badge danger">{{ $c->debt_count }}</span></td>
                            <td class="num money">
                                @if ($c->debt_amount > 0)
                                    <span style="color:#f87171;">{{ fmt_money($c->debt_amount) }}</span>
                                @else
                                    <span style="color:var(--text-dim);">0</span>
                                @endif
                            </td>
                            <td>{!! ring_progress((float) $c->percent_paid) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
