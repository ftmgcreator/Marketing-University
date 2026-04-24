@php
if (! function_exists('fmt_money')) {
    function fmt_money($value): string {
        return number_format((float) $value, 0, '.', ' ');
    }
}
if (! function_exists('percent_class')) {
    function percent_class(float $p): string {
        if ($p >= 80) return 'success';
        if ($p >= 50) return 'warning';
        return 'danger';
    }
}
if (! function_exists('initials')) {
    function initials(string $name): string {
        $parts = preg_split('/\s+/u', trim($name));
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);
        return mb_strtoupper($first.$second) ?: '··';
    }
}
if (! function_exists('rank_class')) {
    function rank_class(int $i): string {
        return match ($i) { 0 => 'gold', 1 => 'silver', 2 => 'bronze', default => '' };
    }
}
if (! function_exists('ring_progress')) {
    function ring_progress(float $percent): string {
        $percent = max(0, min(100, $percent));
        $r = 22; $c = 2 * M_PI * $r; // circumference ≈ 138.23
        $offset = $c * (1 - $percent / 100);
        $cls = percent_class($percent);
        $display = round($percent).'%';

        return <<<HTML
<span class="ring">
    <svg viewBox="0 0 56 56">
        <circle class="track" cx="28" cy="28" r="{$r}"/>
        <circle class="bar {$cls}" cx="28" cy="28" r="{$r}"
                stroke-dasharray="{$c}"
                stroke-dashoffset="{$offset}"/>
    </svg>
    <span class="pct">{$display}</span>
</span>
HTML;
    }
}
@endphp
