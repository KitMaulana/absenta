@props(['status'])

@php 
    $s = $status instanceof \App\Enums\AttendanceStatus ? $status : \App\Enums\AttendanceStatus::from($status); 
    $neonStyle = match ($s) {
        \App\Enums\AttendanceStatus::Hadir => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]',
        \App\Enums\AttendanceStatus::Sakit => 'bg-amber-500/15 text-amber-300 border-amber-500/30 shadow-[0_0_10px_rgba(245,158,11,0.2)]',
        \App\Enums\AttendanceStatus::Izin => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30 shadow-[0_0_10px_rgba(6,182,212,0.2)]',
        \App\Enums\AttendanceStatus::Alpa => 'bg-rose-500/15 text-rose-300 border-rose-500/30 shadow-[0_0_10px_rgba(244,63,94,0.2)]',
        \App\Enums\AttendanceStatus::Dispensasi => 'bg-purple-500/15 text-purple-300 border-purple-500/30 shadow-[0_0_10px_rgba(168,85,247,0.2)]',
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-mono font-semibold border backdrop-blur-md {{ $neonStyle }}">
    <span class="h-1.5 w-1.5 rounded-full shrink-0 animate-pulse" style="background-color: {{ $s->warna() }}; box-shadow: 0 0 6px {{ $s->warna() }}"></span>
    {{ $s->label() }}
</span>