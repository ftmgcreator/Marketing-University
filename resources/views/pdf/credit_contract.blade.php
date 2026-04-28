<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Shartnoma № {{ $contract->contract_number }}</title>
    <style>
        @page { margin: 20mm 20mm 22mm 20mm; }
        body {
            font-family: "DejaVu Serif", "Times New Roman", serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.5;
            text-align: justify;
        }
        .page-date {
            text-align: right;
            font-size: 10pt;
            font-style: italic;
            font-weight: bold;
            margin-bottom: 14px;
        }
        h1.title {
            text-align: center;
            font-size: 13pt;
            font-weight: normal;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        h1.title .num { display: block; margin-top: 2px; }
        .city-row {
            margin-top: 16px;
            margin-bottom: 16px;
            border-top: 1px solid #777;
            border-bottom: 1px solid #777;
            padding: 6px 0;
            font-size: 11pt;
        }
        .city-row table { width: 100%; border-collapse: collapse; }
        .city-row td { padding: 0; }
        .city-row td.right { text-align: right; }
        h2.section {
            text-align: center;
            font-size: 11.5pt;
            font-weight: bold;
            margin: 16px 0 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        p { margin: 7px 0; }
        ul { margin: 6px 0 6px 22px; padding: 0; }
        ul li { margin-bottom: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .info-table td { padding: 3px 4px; vertical-align: top; }
        .info-table td.label { width: 130px; }
        .info-table td.value { font-weight: bold; }
        .amount-box {
            font-weight: bold;
            text-decoration: underline;
            padding: 0 3px;
        }
        table.subjects {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px;
            font-size: 10.5pt;
        }
        table.subjects th, table.subjects td {
            border: 1px solid #555;
            padding: 5px 7px;
            text-align: left;
            vertical-align: top;
        }
        table.subjects th {
            background: #eee;
            font-weight: bold;
        }
        table.subjects td.num { text-align: center; width: 8%; }
        table.subjects td.cr { text-align: center; width: 14%; }
        table.subjects td.amt { text-align: right; width: 22%; font-weight: bold; }
        table.subjects tr.total td { background: #f5f5f5; font-weight: bold; }
        .req-block { vertical-align: top; font-size: 10.5pt; line-height: 1.45; }
        .req-block .heading { font-weight: bold; margin-top: 8px; display: block; }
        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 170px;
            height: 12px;
            vertical-align: bottom;
        }
        strong, b { font-weight: bold; }
    </style>
</head>
<body>

@php
    $u = config('university');
    $dateStr = $contract->contract_date->format('d.m.Y');
    $totalFormatted = number_format($contract->total_amount, 2, ',', ' ');
@endphp

<div class="page-date">{{ $dateStr }}</div>

<h1 class="title">
    Fanlarni o‘qitish yoki qayta o‘qitishga
    <span class="num">SHARTNOMA № {{ $contract->contract_number }}</span>
</h1>

<div class="city-row">
    <table>
        <tr>
            <td>{{ $u['city'] }}</td>
            <td class="right">{{ $dateStr }}</td>
        </tr>
    </table>
</div>

<p>
    O‘zbekiston Respublikasi Vazirlar Mahkamasining 2020-yil 31-dekabrdagi 824-son qarori bilan tasdiqlangan
    “Oliy ta’lim muassasalarida o‘quv jarayoniga kredit-modul tizimini joriy etish tartibi to‘g‘risida”gi hamda
    O‘zbekiston Respublikasi Oliy va o‘rta maxsus ta’lim vazirligining 2018-yil 9-avgustdagi 19-2018-son buyrug‘i
    bilan tasdiqlangan, Adliya vazirligida 2018 yil 26 sentabrda 3069-son bilan ro‘yxatdan o‘tkazilgan
    “Oliy ta’lim muassasalarida talabalar bilimini nazorat qilish va baholash tizimi to‘g‘risida”gi Nizomlarga
    muvofiq, <strong>{{ $u['name'] }}</strong> (keyingi o‘rinlarda “Ta’lim muassasasi”) nomidan rektor (direktor)
    <strong>{{ $u['rector'] }}</strong> bir tomondan,
    <strong>{{ $contract->full_name }}</strong> (keyingi o‘rinlarda “Ta’lim oluvchi”) ikkinchi tomondan,
    birgalikda “Tomonlar” deb ataladigan shaxslar mazkur kontraktni quyidagicha tuzdilar:
</p>

<h2 class="section">1. SHARTNOMA PREDMETI</h2>

<p>1.1. Ta’lim muassasasi Ta’lim oluvchining o‘zlashtirilmagan fan(lar)ini belgilangan semestr davomida yoki alohida belgilangan muddatlarda o‘qitish yoki qayta o‘qitish va nazoratlarini o‘tkazishni o‘z zimmasiga oladi.</p>

<p>1.2. Ta’lim oluvchi o‘qish uchun belgilangan to‘lovni o‘z vaqtida amalga oshirishni va tasdiqlangan o‘quv jadvali bo‘yicha mashg‘ulotlarga to‘liq qatnashib ta’lim olishni o‘z zimmasiga oladi. Ta’lim oluvchining ta’lim ma’lumotlari quyidagicha:</p>

<table class="info-table">
    <tr>
        <td class="label">Ta’lim bosqichi:</td>
        <td class="value">{{ $contract->education_type ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label">Ta’lim shakli:</td>
        <td class="value">{{ $contract->education_form ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label">O‘quv kursi:</td>
        <td class="value">{{ $contract->course ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label">Ta’lim yo‘nalishi:</td>
        <td class="value">
            @php
                $code = $contract->speciality_id
                    ? \App\Models\Speciality::find($contract->speciality_id)?->code
                    : null;
            @endphp
            {{ trim(($code ? $code.' - ' : '').$contract->speciality) }}
        </td>
    </tr>
</table>

<h2 class="section">2. SHARTNOMA QIYMATI VA TO‘LOV MUDDATI</h2>

<p>2.1. Ta’lim muassasasining Ta’lim oluvchini o‘qitish yoki qayta o‘qitish bilan bog‘liq ta’lim xizmatini ko‘rsatish narxi bir kredit qiymatiga nisbatan hisoblanadi. Bir kredit qiymati o‘quv yili davomida ta’lim olish uchun to‘lanadigan umumiy kontrakt summasini o‘quv rejaning bir yillik kredit miqdoriga bo‘lish yo‘li bilan aniqlanadi. Bunda shartnomaning qiymati o‘qitilishi kerak bo‘lgan fan(lar)ning jami kredit miqdorini bir kredit qiymatiga ko‘paytirish orqali aniqlanadi. Bir kredit qiymati ta’lim yonalishlari yoki mutaxassisliklar turlariga qarab turlicha bo‘lishi mumkin.</p>

<p>2.2. Ta’lim oluvchini alohida fan(lar) bo‘yicha o‘qitish yoki qayta o‘qitishning umumiy qiymati <span class="amount-box">{{ $totalFormatted }} so‘m</span> ni tashkil etadi (jami {{ (int) $contract->credits_count }} kredit).</p>

<p>2.3. Shartnomaning 2.2 bandida belgilangan summani o‘qish yoki qayta o‘qish boshlanishidan kamida 3 (uch) kun oldin to‘liq to‘lash talab etiladi.</p>

<h2 class="section">3. TOMONLARNING MAJBURIYATLARI</h2>

<p><strong>3.1. Ta’lim muassasasi:</strong></p>
<ul>
    <li>ta’lim oluvchini alohida fan(lar) bo‘yicha o‘qitish yoki qayta o‘qitish va nazoratlarni topshirish uchun oliy ta’lim tizimi qonunchiligida nazarda tutilgan zarur shart-sharoitlarni yaratish;</li>
    <li>ta’lim oluvchiga qonun bilan belgilangan huquqlarining erkin amalga oshirilishini va ta’lim muassasasi Ustaviga muvofiq majburiyatlarning bajarilishini ta’minlash;</li>
    <li>ta’lim oluvchini tasdiqlangan o‘quv rejasi va dasturlarga muvofiq Davlat ta’lim standarti talablari darajasida o‘qitish hamda o‘rnatilgan tartib asosida o‘zlashtirish darajasini baholash;</li>
    <li>ta’lim oluvchini o‘qitish yoki qayta o‘qitish va nazoratlarni topshirish narxi o‘zgargan taqdirda bu to‘g‘risida ta’lim oluvchini xabardor qilish;</li>
    <li>ta’lim oluvchini o‘qitish yoki qayta o‘qitish va nazoratlarni topshirish uchun to‘lov to‘liq amalga oshirilganidan so‘ng qayta o‘qish va nazoratlarni topshirishga ruxsat berish;</li>
    <li>fanlarni o‘qitish yoki qayta o‘qitish va nazoratlarni topshirish muddatini akademik qarzdorlik hajmidan kelib chiqib belgilash;</li>
    <li>qonunchilikda belgilangan tartibda boshqa majburiyatlar ham kiradi.</li>
</ul>

<p><strong>3.2. Ta’lim oluvchi:</strong></p>
<ul>
    <li>shartnomaning 2.2 bandida belgilangan to‘lov summasini o‘qish yoki qayta o‘qish boshlanishidan kamida 3 (uch) kun oldin to‘lash;</li>
    <li>o‘qish uchun belgilangan to‘lov miqdorini to‘laganlik to‘g‘risidagi bank tasdiqnomasi va shartnomaning bir nusxasini o‘z vaqtida hujjatlarni rasmiylashtirish uchun ta’lim muassasasiga topshirish;</li>
    <li>Ta’lim muassasasi tomonidan tashkil etilgan mashg‘ulotlarga muntazam ravishda qatnashish va davlat ta’lim standartlari talablari asosida bilim olish majburiyatini oladi.</li>
</ul>

<h2 class="section">4. SHARTNOMANI AMAL QILISH MUDDATI VA BEKOR QILISH TARTIBI</h2>

<p>4.1. Shartnoma ikki tomonlama imzolangandan so‘ng kuchga kiradi hamda ta’lim xizmatini taqdim etish yakunlangunga qadar amalda bo‘ladi.</p>

<p>4.2. Shartnoma shartlariga ikkala tomon kelishuviga asosan tuzatish, o‘zgartirish va qo‘shimchalar kiritilishi mumkin.</p>

<p>4.3. Shartnoma tomonlarning o‘zaro roziligi bilan, tomonlardan biri shartnoma shartlarini bajarmaganida, uzrli sabablar bilan Ta’lim oluvchining tashabbusiga ko‘ra va amaldagi qonunchilikda ko‘rsatilgan boshqa hollarda bekor qilinishi mumkin.</p>

<h2 class="section">5. BOSHQA SHARTLAR VA NIZOLARNI HAL QILISH TARTIBI</h2>

<p>5.1. Shartnomani bajarish jarayonida tomonlar o‘rtasida kelib chiqadigan nizolar muzokaralar yo‘li bilan yoki qonunchilikda belgilangan tartibda hal etiladi.</p>

<p>5.2. Shartnoma 2 (ikki) nusxada, tomonlarning har biri uchun bir nusxadan tuzildi va ikkala nusxa ham bir xil huquqiy kuchga ega.</p>

<h2 class="section">6. TOMONLARNING REKVIZITLARI VA IMZOLARI</h2>

<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td class="req-block" style="width:50%; padding-right:8px;">
            <strong>6.1. Ta’lim muassasasi:</strong><br>
            {{ $u['name'] }}
            <span class="heading">Pochta manzili:</span>
            {{ $u['postal_code'] }}<br>
            {{ $u['address'] }}<br>
            {{ $u['phones'] }}<br>
            {{ $u['email'] }}

            <span class="heading">Bank rekvizitlari:</span>
            {{ $u['bank']['org'] }}<br>
            Bank: {{ $u['bank']['name'] }}<br>
            H/R: {{ $u['bank']['account'] }}<br>
            (MFO): {{ $u['bank']['mfo'] }}<br>
            (INN): {{ $u['bank']['inn'] }}
        </td>
        <td class="req-block" style="width:50%; padding-left:8px;">
            <strong>6.2. Ta’lim oluvchi:</strong><br>
            F.I.Sh.: {{ $contract->full_name }}<br>
            @if($contract->address)
                Yashash manzili: {{ $contract->address }}<br>
            @endif
            @if($contract->passport)
                Pasport ma’lumotlari: {{ $contract->passport }}<br>
            @endif
            @if($contract->jshshir)
                Talaba JSHSHIR kodi: {{ $contract->jshshir }}<br>
            @endif
            Talaba kodi: {{ $contract->student_code ?? '' }}<br>
            @if($contract->phone)
                Telefon raqami: {{ $contract->phone }}<br>
            @endif
            <br><br>
            Ta’lim oluvchining imzosi: <span class="signature-line"></span>
        </td>
    </tr>
</table>

<p style="margin-top:30px;">
    <strong>Ta’lim muassasasi rahbari:</strong> <span class="signature-line"></span><br>
    {{ $u['rector'] }}
</p>

<p style="margin-top:14px;">
    <strong>Bosh hisobchi:</strong> <span class="signature-line"></span>
</p>

</body>
</html>
