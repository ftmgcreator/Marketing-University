<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Kredit modul shartnomasi {{ $contract->contract_number }}</title>
    <style>
        @page { margin: 25mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; line-height: 1.45; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        h2 { font-size: 13px; margin: 18px 0 8px; border-bottom: 1px solid #999; padding-bottom: 3px; }
        .meta { text-align: center; margin-bottom: 16px; color: #555; font-size: 11px; }
        .meta strong { color: #111; }
        table.kv { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.kv td { padding: 5px 6px; vertical-align: top; border-bottom: 1px dotted #ccc; }
        table.kv td.label { width: 38%; color: #555; }
        table.kv td.value { font-weight: 600; }
        table.calc { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.calc th, table.calc td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        table.calc th { background: #f1f1f1; font-size: 11px; }
        table.calc td.amount { text-align: right; font-weight: 600; }
        .total-row td { background: #f7f7f7; font-size: 13px; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; padding-top: 30px; vertical-align: top; }
        .sign-line { border-top: 1px solid #333; width: 70%; padding-top: 4px; font-size: 10px; color: #555; }
        p { margin: 6px 0; }
    </style>
</head>
<body>

<h1>Kredit modul bo'yicha shartnoma</h1>
<div class="meta">
    Shartnoma № <strong>{{ $contract->contract_number }}</strong>
    &nbsp;·&nbsp;
    Sana: <strong>{{ $contract->contract_date->format('d.m.Y') }}</strong>
</div>

<p>
    Mazkur shartnoma <strong>Toshkent xalqaro Sharq universiteti</strong> (keyingi o'rinlarda — Universitet)
    bilan quyidagi talaba o'rtasida o'qitishning kredit modul tizimi bo'yicha qayta o'qish uchun tuzildi.
</p>

<h2>Talaba ma'lumotlari</h2>
<table class="kv">
    <tr>
        <td class="label">F.I.Sh.</td>
        <td class="value">{{ $contract->full_name }}</td>
    </tr>
    <tr>
        <td class="label">Mutaxassislik</td>
        <td class="value">{{ $contract->speciality }}</td>
    </tr>
    <tr>
        <td class="label">Guruh</td>
        <td class="value">{{ $contract->group_name }}</td>
    </tr>
    @if($contract->jshshir)
    <tr>
        <td class="label">JSHSHIR</td>
        <td class="value">{{ $contract->jshshir }}</td>
    </tr>
    @endif
    @if($contract->passport)
    <tr>
        <td class="label">Pasport</td>
        <td class="value">{{ $contract->passport }}</td>
    </tr>
    @endif
</table>

<h2>Kredit modul tafsilotlari</h2>
<table class="calc">
    <thead>
    <tr>
        <th style="width: 50%;">Fan / Modul</th>
        <th style="width: 15%;">Kreditlar</th>
        <th style="width: 17%;">1 kredit narxi</th>
        <th style="width: 18%;">Summa</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ $contract->subject_name ?? '—' }}</td>
        <td>{{ $contract->credits_count }}</td>
        <td class="amount">{{ number_format($contract->price_per_credit, 0, '.', ' ') }} so'm</td>
        <td class="amount">{{ number_format($contract->total_amount, 0, '.', ' ') }} so'm</td>
    </tr>
    <tr class="total-row">
        <td colspan="3"><strong>Jami to'lanadigan summa</strong></td>
        <td class="amount"><strong>{{ number_format($contract->total_amount, 0, '.', ' ') }} so'm</strong></td>
    </tr>
    </tbody>
</table>

<h2>To'lov holati</h2>
<table class="kv">
    <tr>
        <td class="label">Holati</td>
        <td class="value">
            @switch($contract->payment_status)
                @case('paid') To'langan @break
                @case('partial') Qisman to'langan @break
                @default Kutilmoqda
            @endswitch
        </td>
    </tr>
    <tr>
        <td class="label">To'langan summa</td>
        <td class="value">{{ number_format($contract->paid_amount, 0, '.', ' ') }} so'm</td>
    </tr>
    <tr>
        <td class="label">Qoldiq</td>
        <td class="value">{{ number_format(max(0, $contract->total_amount - $contract->paid_amount), 0, '.', ' ') }} so'm</td>
    </tr>
</table>

@if($contract->notes)
    <h2>Izoh</h2>
    <p>{{ $contract->notes }}</p>
@endif

<table class="signatures">
    <tr>
        <td>
            <strong>Universitet nomidan</strong><br><br><br>
            <div class="sign-line">imzo / sana</div>
        </td>
        <td>
            <strong>Talaba</strong><br>
            {{ $contract->full_name ?? '' }}<br><br>
            <div class="sign-line">imzo / sana</div>
        </td>
    </tr>
</table>

</body>
</html>
