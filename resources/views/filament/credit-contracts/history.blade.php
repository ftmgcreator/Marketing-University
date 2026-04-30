@php
    use App\Models\User;

    $labels = [
        'contract_number' => 'Shartnoma raqami',
        'contract_date' => 'Sana',
        'full_name' => 'F.I.Sh.',
        'jshshir' => 'JSHSHR',
        'phone' => 'Telefon',
        'speciality' => 'Mutaxassislik',
        'faculty' => 'Fakultet',
        'education_form' => "Ta'lim shakli",
        'course' => 'Kurs',
        'group_name' => 'Guruh',
        'credits_count' => 'Kreditlar soni',
        'total_amount' => 'Umumiy summa',
        'payment_status' => "To'lov holati",
        'paid_amount' => "To'langan summa",
        'notes' => 'Izoh',
    ];

    $statusLabels = [
        'pending' => 'Kutilmoqda',
        'partial' => "Qisman to'langan",
        'paid' => "To'langan",
    ];

    $eventLabels = [
        'created' => 'Yaratildi',
        'updated' => "O'zgartirildi",
        'deleted' => "O'chirildi",
    ];

    $eventColors = [
        'created' => '#10b981',
        'updated' => '#f59e0b',
        'deleted' => '#f43f5e',
    ];

    $formatValue = function ($key, $value) use ($statusLabels) {
        if ($value === null || $value === '') {
            return '—';
        }
        if ($key === 'payment_status') {
            return $statusLabels[$value] ?? $value;
        }
        if (in_array($key, ['total_amount', 'paid_amount'], true)) {
            return number_format((int) $value, 0, '.', ' ').' so\'m';
        }
        if ($key === 'contract_date') {
            try {
                return \Carbon\Carbon::parse($value)->format('d.m.Y');
            } catch (\Throwable) {
                return $value;
            }
        }
        return (string) $value;
    };

    $roleLabels = User::ROLE_LABELS;
    $totalCount = $activities->count();
@endphp

<div style="display:flex; flex-direction:column; gap:20px; max-height:60vh; overflow-y:auto; padding:8px 8px 8px 0;">
    @forelse ($activities as $i => $activity)
        @php
            $event = $activity->event ?? 'updated';
            $causer = $activity->causer;
            $causerName = $causer?->name ?? 'Tizim';
            $causerRole = $causer?->role ? ($roleLabels[$causer->role] ?? $causer->role) : null;
            $changes = $activity->properties->get('attributes', []);
            $old = $activity->properties->get('old', []);
            $number = $totalCount - $i;
            $dotColor = $eventColors[$event] ?? '#9ca3af';
        @endphp

        <div style="border:1px solid rgba(148,163,184,0.25); border-radius:10px; background:rgba(255,255,255,0.03); padding:16px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(148,163,184,0.18); flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:9999px; background:rgba(99,102,241,0.15); color:#6366f1; font-size:12px; font-weight:700;">#{{ $number }}</span>
                    <span style="display:inline-block; width:8px; height:8px; border-radius:9999px; background:{{ $dotColor }};"></span>
                    <span style="font-weight:600; font-size:14px;">{{ $eventLabels[$event] ?? $event }}</span>
                    <span style="color:#94a3b8; font-size:12px;">·</span>
                    <span style="font-size:14px;">{{ $causerName }}</span>
                    @if ($causerRole)
                        <span style="font-size:11px; padding:2px 8px; border-radius:6px; background:rgba(99,102,241,0.15); color:#818cf8;">{{ $causerRole }}</span>
                    @endif
                </div>
                <span style="font-size:12px; color:#94a3b8; white-space:nowrap;">{{ $activity->created_at?->format('d.m.Y H:i') }}</span>
            </div>

            @if ($event === 'created')
                <div style="font-size:13px; color:#94a3b8;">Shartnoma yaratildi.</div>
            @elseif (! empty($changes))
                <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                    @foreach ($changes as $key => $newValue)
                        @php
                            $oldValue = $old[$key] ?? null;
                            $label = $labels[$key] ?? $key;
                        @endphp
                        <div style="display:flex; align-items:flex-start; gap:8px; flex-wrap:wrap; padding:6px 0; border-bottom:1px dashed rgba(148,163,184,0.12);">
                            <span style="font-weight:600; min-width:140px;">{{ $label }}:</span>
                            <span style="color:#f87171; text-decoration:line-through;">{{ $formatValue($key, $oldValue) }}</span>
                            <span style="color:#94a3b8;">→</span>
                            <span style="color:#34d399; font-weight:500;">{{ $formatValue($key, $newValue) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="font-size:12px; color:#94a3b8;">O'zgarish yo'q.</div>
            @endif
        </div>
    @empty
        <div style="text-align:center; font-size:14px; color:#94a3b8; padding:32px 0;">Bu shartnoma uchun tarix yo'q.</div>
    @endforelse
</div>
