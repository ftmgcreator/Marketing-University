@extends('layouts.public')
@include('public._helpers')

@section('title', $faculty->name.' — Fakultet statistikasi')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('public.dashboard') }}">Bosh sahifa</a>
        <span class="sep">/</span>
        <span>{{ $faculty->name }}</span>
    </div>

    <div class="page-head">
        <h1 class="page-title">{{ $faculty->name }}</h1>
        <p class="page-subtitle">Fakultet bo'yicha kafedralar kesimida shartnoma to'lovlari</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="label">Talabalar</div>
            <div class="value">{{ number_format($faculty->student_count, 0, '.', ' ') }}</div>
            <div class="sub">
                <span class="badge success">{{ $faculty->paid_count }} to'lagan</span>
                <span class="badge danger">{{ $faculty->debt_count }} qarzdor</span>
            </div>
        </div>
        <div class="stat-card neutral">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="label">Shartnoma</div>
            <div class="value money">{{ fmt_money($faculty->contract_amount) }}<span class="currency">so'm</span></div>
            <div class="sub">{{ $faculty->department_count }} kafedra · {{ $faculty->group_count }} guruh</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="label">To'langan</div>
            <div class="value money">{{ fmt_money($faculty->paid_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge success">{{ $faculty->percent_paid }}% bajarilgan</span></div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="label">Qoldiq</div>
            <div class="value money">{{ fmt_money($faculty->debt_amount) }}<span class="currency">so'm</span></div>
            <div class="sub"><span class="badge danger">{{ $faculty->debt_count }} qarzdor</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                Kafedralar
            </h2>
            <span class="meta">{{ $departments->count() }} ta kafedra</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th class="left">Kafedra nomi</th>
                        <th class="num">Talaba</th>
                        <th class="num">Shartnoma</th>
                        <th class="num">To'langan</th>
                        <th class="num">Qoldiq</th>
                        <th>Bajarilish</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $i => $d)
                        <tr>
                            <td class="idx"><span class="rank {{ rank_class($i) }}">{{ $i + 1 }}</span></td>
                            <td class="left">
                                <span class="name-cell">
                                    <span class="avatar">{{ mb_strtoupper(mb_substr($d->name, 0, 2)) }}</span>
                                    <a class="row-link" href="{{ route('public.department', $d->slug) }}">{{ $d->name }}</a>
                                </span>
                            </td>
                            <td class="num"><span class="badge neutral">{{ number_format($d->student_count, 0, '.', ' ') }}</span></td>
                            <td class="num money">{{ fmt_money($d->contract_amount) }}</td>
                            <td class="num money" style="color:#34d399;">{{ fmt_money($d->paid_amount) }}</td>
                            <td class="num money">
                                @if ($d->debt_amount > 0)
                                    <span style="color:#f87171;">{{ fmt_money($d->debt_amount) }}</span>
                                @else
                                    <span style="color:var(--text-dim);">0</span>
                                @endif
                            </td>
                            <td>{!! ring_progress((float) $d->percent_paid) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
