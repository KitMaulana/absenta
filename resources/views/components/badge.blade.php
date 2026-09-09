@props(['status'])

@php $s = $status instanceof \App\Enums\AttendanceStatus ? $status : \App\Enums\AttendanceStatus::from($status); @endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $s->badgeClass() }}">
    <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $s->warna() }}"></span>
    {{ $s->label() }}
</span>
