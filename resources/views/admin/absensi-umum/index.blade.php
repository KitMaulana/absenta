@extends('layouts.admin')
@section('title', 'Absensi Umum Harian')
@section('subtitle', $namaHari.', '.\App\Support\Tanggal::pendek($tanggal))

@php
    use App\Enums\AttendanceStatus;
@endphp

@section('content')
<div class="space-y-5" x-data="{ modalLibur: false }">
    {{-- Filter Tanggal & Status --}}
    <x-card>
        <div class="flex flex-wrap items-end justify-between gap-3">
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
            </form>

            <div class="flex flex-wrap items-center gap-2">
                @if (! $libur)
                    <button type="button" @click="modalLibur = true"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 shadow-2xs hover:bg-amber-100 transition">
                        <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <span>Tandai Hari Libur</span>
                    </button>
                @else
                    <form method="POST" action="{{ route('admin.absensi-umum.batal-libur') }}"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status libur untuk tanggal {{ \App\Support\Tanggal::pendek($tanggal) }}?')">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 shadow-2xs hover:bg-rose-100 transition">
                            <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Batalkan Status Libur</span>
                        </button>
                    </form>
                @endif

                @if ($libur)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-300">
                        🏖️ Libur: {{ $libur->keterangan }}
                    </span>
                @elseif ($sudahDiinput)
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
        </div>

        @if ($libur)
            <div class="mt-3.5 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-amber-900">
                <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>Tanggal ini ditetapkan sebagai hari libur: <strong>{{ $libur->keterangan }}</strong>. Presensi dinonaktifkan, absensi mapel otomatis diliburkan, dan siswa tidak dikenakan status alpa/sakit/izin.</span>
            </div>
        @elseif (! $hariAktif)
            <div class="mt-3.5 flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <span>Hari <strong>{{ $namaHari }}</strong> bukan merupakan hari aktif sekolah berdasarkan pengaturan kalender kelas.</span>
            </div>
        @endif
    </x-card>

    @if ($libur)
        {{-- Tampilan Ketika Hari Ini Ditetapkan Libur --}}
        <x-card padat>
            <div class="p-8 text-center sm:p-12">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-3xl shadow-xs border border-amber-200">
                    🏖️
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 sm:text-xl">Hari Ini Ditetapkan Sebagai Hari Libur</h3>
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-4 py-1 text-xs font-bold text-amber-800">
                    {{ $libur->keterangan }}
                </div>
                <p class="mx-auto mt-3 max-w-lg text-xs text-slate-500 leading-relaxed">
                    Seluruh aktivitas presensi dinonaktifkan pada hari libur. Tidak ada absensi siswa (Hadir, Sakit, Izin, Alpa, maupun Dispensasi) yang dicatat atau dihitung ke dalam persentase kehadiran.
                    Absensi per mata pelajaran pada tanggal ini juga otomatis diliburkan.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" @click="modalLibur = true"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <span>Ubah Keterangan Libur</span>
                    </button>
                    <form method="POST" action="{{ route('admin.absensi-umum.batal-libur') }}"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status hari libur pada tanggal ini?')">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 shadow-2xs hover:bg-rose-100 transition">
                            <svg class="h-3.5 w-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batalkan Status Libur</span>
                        </button>
                    </form>
                </div>
            </div>
        </x-card>
    @elseif ($siswa->isEmpty())
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

    {{-- Modal Tandai / Ubah Hari Libur --}}
    <div x-show="modalLibur" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="modalLibur" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
            <div class="flex min-h-full items-end justify-center text-center sm:items-center sm:p-0">
                <div x-show="modalLibur" x-transition @click.outside="modalLibur = false"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                    <form method="POST" action="{{ route('admin.absensi-umum.libur.set') }}">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-amber-700 text-xl font-bold">
                                    🏖️
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900" id="modal-title">
                                        {{ $libur ? 'Ubah Keterangan Libur' : 'Tetapkan Hari Libur' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ \App\Support\Tanggal::panjang($tanggal) }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label for="keterangan_libur" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                        Keterangan Hari Libur <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="keterangan_libur" name="keterangan" type="text" required maxlength="150"
                                           value="{{ $libur?->keterangan ?? 'Libur Sekolah' }}"
                                           placeholder="Contoh: Libur Nasional / Peringatan Hari Guru / Cuti Bersama"
                                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-900">
                                    <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-950">
                                        <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                        <span>Dampak Penetapan Libur:</span>
                                    </div>
                                    <ul class="list-disc pl-4 space-y-1 text-amber-800">
                                        <li>Absensi per mapel pada tanggal ini otomatis diliburkan.</li>
                                        <li>Status libur akan ditampilkan di Web Publik.</li>
                                        <li>Tidak ada siswa yang dihitung alpa, sakit, atau izin pada hari ini.</li>
                                        @if ($sudahDiinput)
                                            <li class="font-semibold text-rose-700">Data presensi yang sempat diinput pada tanggal ini akan dibersihkan secara otomatis.</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50/70 px-6 py-4 rounded-b-2xl">
                            <button type="button" @click="modalLibur = false"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-amber-500/25 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Simpan Status Libur</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
