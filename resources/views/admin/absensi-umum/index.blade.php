@extends('layouts.admin')
@section('title', 'Absensi Umum Harian')
@section('subtitle', $namaHari.', '.\App\Support\Tanggal::pendek($tanggal))

@php
    use App\Enums\AttendanceStatus;
@endphp

@section('content')
<div class="space-y-5">
    {{-- Filter Tanggal & Status --}}
    <x-card>
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tanggal" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Absensi</label>
                <div class="relative">
                    <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal->toDateString() }}" max="{{ now()->toDateString() }}"
                           class="rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                Tampilkan
            </button>
            <a href="{{ route('admin.absensi-umum.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                Hari Ini
            </a>

            <div class="ml-auto flex items-center gap-2">
                @if ($sudahDiinput)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/60">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Sudah Diinput
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200/60">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        Belum Diinput
                    </span>
                @endif
            </div>
        </form>

        @if ($libur)
            <div class="mt-3.5 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-amber-900">
                <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>Tanggal ini terdaftar sebagai hari libur: <strong>{{ $libur->keterangan }}</strong>. Absensi tetap dapat dicatat, namun tidak dihitung dalam hari efektif.</span>
            </div>
        @elseif (! $hariAktif)
            <div class="mt-3.5 flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <span>Hari <strong>{{ $namaHari }}</strong> bukan merupakan hari aktif sekolah berdasarkan pengaturan kalender kelas.</span>
            </div>
        @endif
    </x-card>

    @if ($siswa->isEmpty())
        <x-card><x-kosong pesan="Belum ada data siswa aktif. Tambahkan siswa terlebih dahulu di menu Master Data." ikon="🧑‍🎓" /></x-card>
    @else
        <form method="POST" action="{{ route('admin.absensi-umum.store') }}"
              x-data="absensiUmum()" x-init="hitung()">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

            <x-card padat>
                <x-slot:judul>
                    <div class="flex items-center gap-2">
                        <span>Daftar Presensi</span>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $siswa->count() }} Siswa</span>
                    </div>
                </x-slot:judul>
                <x-slot:aksi>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="hidden sm:flex items-center gap-1.5 rounded-lg bg-slate-100/80 p-1 text-xs">
                            <span class="px-1.5 py-0.5 rounded text-emerald-800 font-bold bg-emerald-100">H: <span x-text="jumlah.hadir"></span></span>
                            <span class="px-1.5 py-0.5 rounded text-amber-800 font-bold bg-amber-100">S: <span x-text="jumlah.sakit"></span></span>
                            <span class="px-1.5 py-0.5 rounded text-blue-800 font-bold bg-blue-100">I: <span x-text="jumlah.izin"></span></span>
                            <span class="px-1.5 py-0.5 rounded text-rose-800 font-bold bg-rose-100">A: <span x-text="jumlah.alpa"></span></span>
                            <span class="px-1.5 py-0.5 rounded text-violet-800 font-bold bg-violet-100">D: <span x-text="jumlah.dispensasi"></span></span>
                        </div>
                        <button type="button" @click="tandaiSemuaHadir()"
                                class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Tandai Semua Hadir</span>
                        </button>
                    </div>
                </x-slot:aksi>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-xs">
                        <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                                <th class="px-4 py-3 font-bold">Nama Siswa</th>
                                <th class="px-4 py-3 text-center font-bold">Status Kehadiran</th>
                                <th class="px-4 py-3 font-bold">Keterangan Tambahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($siswa as $s)
                                @php
                                    $rec = $tersimpan->get($s->id);
                                    $nilai = $rec?->status->value ?? AttendanceStatus::Hadir->value;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-4 py-3 tabular-nums font-bold text-slate-400 text-center">{{ $s->no_absen }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        <div class="flex items-center gap-2.5">
                                            <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-700 shrink-0">
                                                {{ $s->no_absen }}
                                            </div>
                                            <span class="truncate">{{ $s->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-1.5">
                                            @foreach (AttendanceStatus::cases() as $status)
                                                <label class="cursor-pointer select-none">
                                                    <input type="radio" class="peer sr-only" name="status[{{ $s->id }}]"
                                                           value="{{ $status->value }}" @checked($nilai === $status->value)
                                                           @change="hitung()">
                                                    <span class="inline-flex min-w-[2.2rem] items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-center text-xs font-bold text-slate-600 shadow-2xs hover:bg-slate-50 peer-checked:text-white peer-checked:shadow-sm {{ $status->peerClass() }} transition-all"
                                                          title="{{ $status->label() }}">{{ $status->kode() }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input name="keterangan[{{ $s->id }}]" value="{{ $rec?->keterangan }}"
                                               placeholder="Opsional (mis. sakit flu, izin acara keluarga)" maxlength="150"
                                               class="w-full min-w-[14rem] rounded-xl border-slate-200 text-xs shadow-2xs placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer Simpan Absensi --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 bg-slate-50/50 px-5 py-4 rounded-b-2xl">
                    <p class="text-xs text-slate-500 text-center sm:text-left">
                        Default terisi <strong>Hadir</strong>. Cukup ubah siswa yang tidak hadir lalu klik simpan.
                        @if ($terakhir = $tersimpan->max('updated_at'))
                            <span class="block text-slate-400 mt-0.5">Terakhir diperbarui {{ \App\Support\Tanggal::pendek($terakhir) }} pukul {{ $terakhir->format('H:i') }} WIB.</span>
                        @endif
                    </p>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:from-indigo-500 hover:to-indigo-600 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Simpan Absensi Umum</span>
                    </button>
                </div>
            </x-card>
        </form>
    @endif
</div>

@push('scripts')
<script>
function absensiUmum() {
    return {
        jumlah: { hadir: 0, sakit: 0, izin: 0, alpa: 0, dispensasi: 0 },

        hitung() {
            const baru = { hadir: 0, sakit: 0, izin: 0, alpa: 0, dispensasi: 0 };
            this.$el.querySelectorAll('input[type=radio]:checked').forEach(el => {
                if (baru[el.value] !== undefined) baru[el.value]++;
            });
            this.jumlah = baru;
        },

        tandaiSemuaHadir() {
            this.$el.querySelectorAll('input[type=radio][value=hadir]').forEach(el => { el.checked = true; });
            this.hitung();
        },
    };
}
</script>
@endpush
@endsection
