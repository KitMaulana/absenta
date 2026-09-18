@extends('layouts.admin')
@section('title', 'Absensi per Mata Pelajaran')
@section('subtitle', $namaHari.', '.\App\Support\Tanggal::pendek($tanggal))

@php
    use App\Enums\AttendanceStatus;
    $statusLain = AttendanceStatus::tidakHadir();
@endphp

@section('content')
<div class="space-y-5">
    {{-- Filter Tanggal --}}
    <x-card>
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tanggal" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Pelajaran</label>
                <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal->toDateString() }}" max="{{ now()->toDateString() }}"
                       class="rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                Tampilkan
            </button>
            <a href="{{ route('admin.absensi-mapel.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                Hari Ini
            </a>

            <div class="ml-auto flex items-center gap-2">
                @if ($libur)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-300">
                        🏖️ Libur: {{ $libur->keterangan }}
                    </span>
                @elseif ($umumAda)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/60">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Absensi umum hari ini sudah diinput
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200/60">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        Absensi umum hari ini belum diinput
                    </span>
                @endif
            </div>
        </form>

        @if ($libur)
            <div class="mt-3.5 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-amber-900">
                <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>Tanggal ini terdaftar sebagai hari libur: <strong>{{ $libur->keterangan }}</strong>. Seluruh jam pelajaran otomatis diliburkan.</span>
            </div>
        @endif
    </x-card>

    @if ($libur)
        <x-card padat>
            <div class="p-8 text-center sm:p-12">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-3xl shadow-xs border border-amber-200">
                    🏖️
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 sm:text-xl">Hari Ini Libur — Absensi Mata Pelajaran Ditiadakan</h3>
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-4 py-1 text-xs font-bold text-amber-800">
                    {{ $libur->keterangan }}
                </div>
                <p class="mx-auto mt-3 max-w-lg text-xs text-slate-500 leading-relaxed">
                    Karena tanggal {{ \App\Support\Tanggal::panjang($tanggal) }} telah ditetapkan sebagai hari libur di Absensi Umum, seluruh jam mata pelajaran pada hari ini otomatis diliburkan dan tidak dapat diinput absensi.
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('admin.absensi-umum.index', ['tanggal' => $tanggal->toDateString()]) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                        <span>Buka Absensi Umum</span>
                    </a>
                </div>
            </div>
        </x-card>
    @elseif ($sesiMapel->isEmpty())
        <x-card>
            <x-kosong pesan="Tidak ada jadwal pelajaran yang tercatat untuk hari {{ $namaHari }}." ikon="🕘">
                <a href="{{ route('admin.jadwal.index') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    <span>Atur Jadwal Pelajaran</span>
                </a>
            </x-kosong>
        </x-card>
    @elseif ($siswa->isEmpty())
        <x-card><x-kosong pesan="Belum ada siswa aktif di kelas ini." ikon="🧑‍🎓" /></x-card>
    @else
        <div x-data="{ sesiAktif: '{{ $sesiAktif }}' }">
            {{-- Navigasi Tab Sesi Mata Pelajaran (Per Mapel) --}}
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pilih Mata Pelajaran Hari {{ $namaHari }}</p>
                    <span class="text-xs text-slate-400">{{ $sesiMapel->count() }} Mata Pelajaran ({{ $jadwal->count() }} JP)</span>
                </div>
                <div class="flex gap-2.5 overflow-x-auto pb-2">
                    @foreach ($sesiMapel as $sesi)
                        <button type="button" @click="sesiAktif = '{{ $sesi->id }}'"
                                class="flex shrink-0 items-center gap-3 rounded-2xl border p-3 text-xs font-bold transition-all shadow-2xs text-left"
                                :class="sesiAktif === '{{ $sesi->id }}'
                                    ? 'border-slate-900 bg-slate-900 text-white shadow-md'
                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300'">
                            <span class="grid h-9 w-9 place-items-center rounded-xl text-xs font-black text-white shrink-0 shadow-2xs" style="background-color: {{ $sesi->subject->warna }}">
                                {{ $sesi->subject->singkatan }}
                            </span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-sm tracking-tight truncate">{{ $sesi->subject->nama }}</span>
                                    @if ($sesi->terisi)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-bold text-emerald-400">
                                            ✓ Terisi
                                        </span>
                                    @elseif ($sesi->terisi_sebagian)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-400">
                                            ~ Sebagian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-500/20 px-2 py-0.5 text-[10px] font-bold text-slate-400">
                                            ! Belum
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-medium opacity-80 mt-0.5">
                                    {{ $sesi->label_jp }} {{ $sesi->rentang_waktu ? '· ' . $sesi->rentang_waktu : '' }}
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach ($sesiMapel as $sesi)
                <div x-show="sesiAktif === '{{ $sesi->id }}'" x-cloak>
                    <form method="POST" action="{{ route('admin.absensi-mapel.store') }}"
                          x-data="absensiSesi('{{ $sesi->id }}', {{ json_encode($sesi->schedule_ids) }})" x-init="root = $el; hitung()">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                        <input type="hidden" name="sesi_id" value="{{ $sesi->id }}">
                        @foreach ($sesi->schedule_ids as $sid)
                            <input type="hidden" name="schedule_ids[]" value="{{ $sid }}">
                        @endforeach

                        <x-card padat>
                            <x-slot:judul>
                                <div class="flex items-center gap-3">
                                    <span class="grid h-8 w-8 place-items-center rounded-xl text-xs font-black text-white shadow-2xs" style="background-color: {{ $sesi->subject->warna }}">
                                        {{ $sesi->subject->singkatan }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm sm:text-base">{{ $sesi->subject->nama }}</span>
                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700 border border-indigo-100">
                                                {{ $sesi->total_jp }} Jam Pelajaran
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            {{ $sesi->label_jp }}
                                            @if ($sesi->rentang_waktu) &middot; Pukul {{ $sesi->rentang_waktu }} WIB @endif
                                            @if ($sesi->guru_pengampu) &middot; Guru: <strong>{{ $sesi->guru_pengampu }}</strong> @endif
                                        </p>
                                    </div>
                                </div>
                            </x-slot:judul>
                            <x-slot:aksi>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="hidden sm:inline-flex items-center gap-1 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200/60">
                                        Hadir: <strong class="tabular-nums" x-text="jumlahHadir"></strong> / {{ $siswa->count() }}
                                    </span>
                                    <button type="button" @click="centangSemua()"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span>Centang Semua</span>
                                    </button>
                                    <button type="button" @click="salin('umum')"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                        <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/></svg>
                                        <span>Salin dari Absensi Umum</span>
                                    </button>
                                    @if (! $loop->first)
                                        <button type="button" @click="salin('jp_sebelumnya')"
                                                class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            <span>Salin dari Mapel Sebelumnya</span>
                                        </button>
                                    @endif
                                </div>
                            </x-slot:aksi>

                            {{-- Pemberitahuan penting input per mapel --}}
                            <div class="border-b border-indigo-100 bg-indigo-50/60 px-5 py-2.5 text-xs text-indigo-900 flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>
                                    Absensi ini berlaku untuk <strong>seluruh {{ $sesi->total_jp }} Jam Pelajaran</strong> ({{ implode(', ', array_map(fn($n) => 'JP '.$n, $sesi->jam_ke_list)) }}). Siswa yang dinyatakan Hadir otomatis tercatat hadir di semua {{ $sesi->total_jp }} JP.
                                </span>
                            </div>

                            <p x-show="pesan" x-cloak x-text="pesan" class="border-b border-amber-200 bg-amber-50 px-5 py-2.5 text-xs font-semibold text-amber-800"></p>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-xs">
                                    <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                                        <tr>
                                            <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                                            <th class="px-4 py-3 font-bold">Nama Siswa</th>
                                            <th class="px-4 py-3 text-center font-bold">Hadir ({{ $sesi->total_jp }} JP)</th>
                                            <th class="px-4 py-3 font-bold">Status Jika Tidak Hadir</th>
                                            <th class="px-4 py-3 font-bold">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($siswa as $s)
                                            @php
                                                $rec = $sesi->data_tersimpan?->get($s->id);
                                                $nilai = $rec?->status->value ?? $umum[$s->id] ?? AttendanceStatus::Hadir->value;
                                                $hadir = $nilai === AttendanceStatus::Hadir->value;
                                            @endphp
                                            <tr class="hover:bg-slate-50/60 transition" x-data="{ hadir: {{ $hadir ? 'true' : 'false' }} }">
                                                <td class="px-4 py-3 tabular-nums font-bold text-slate-400 text-center">{{ $s->no_absen }}</td>
                                                <td class="px-4 py-3 font-bold text-slate-900">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-700 shrink-0">
                                                            {{ $s->no_absen }}
                                                        </div>
                                                        <span class="truncate">{{ $s->nama }}</span>
                                                    </div>
                                                </td>

                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" data-hadir="{{ $s->id }}" x-model="hadir" @change="sinkron({{ $s->id }}, hadir); hitung()"
                                                           class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 shadow-2xs transition">
                                                </td>

                                                <td class="px-4 py-3">
                                                    <input type="radio" class="sr-only" name="status[{{ $s->id }}]" value="hadir" @checked($hadir) data-status-hadir="{{ $s->id }}">

                                                    <div class="flex flex-wrap gap-1.5 transition-opacity duration-150" :class="hadir ? 'opacity-30 pointer-events-none' : ''">
                                                        @foreach ($statusLain as $status)
                                                            <label class="cursor-pointer select-none">
                                                                <input type="radio" class="peer sr-only" name="status[{{ $s->id }}]" value="{{ $status->value }}"
                                                                       data-status="{{ $s->id }}" @checked($nilai === $status->value) @change="hadir = false; hitung()">
                                                                <span class="inline-flex min-w-[2.2rem] items-center justify-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-center text-xs font-bold text-slate-600 shadow-2xs hover:bg-slate-50 peer-checked:text-white peer-checked:shadow-sm {{ $status->peerClass() }} transition-all"
                                                                      title="{{ $status->label() }}">{{ $status->kode() }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </td>

                                                <td class="px-4 py-3">
                                                    <input name="keterangan[{{ $s->id }}]" data-ket="{{ $s->id }}" value="{{ $rec?->keterangan }}"
                                                           placeholder="Opsional" maxlength="150" :disabled="hadir"
                                                           class="w-full min-w-[12rem] rounded-xl border-slate-200 text-xs shadow-2xs placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50/60 disabled:text-slate-400">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Footer Simpan Sesi Mapel --}}
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 bg-slate-50/50 px-5 py-4 rounded-b-2xl">
                                <p class="text-xs text-slate-500 text-center sm:text-left">
                                    Centang = Hadir. Yang tidak dicentang wajib dipilih status S / I / A / D.
                                    @if ($terakhir = $sesi->terakhir_diperbarui)
                                        <span class="block text-slate-400 mt-0.5">Terakhir diperbarui {{ \App\Support\Tanggal::pendek($terakhir) }} pukul {{ $terakhir->format('H:i') }} WIB.</span>
                                    @endif
                                </p>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:from-indigo-500 hover:to-indigo-600 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span>Simpan Absensi {{ $sesi->subject->singkatan }} ({{ $sesi->total_jp }} JP)</span>
                                </button>
                            </div>
                        </x-card>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function absensiSesi(sesiId, scheduleIds) {
    return {
        sesiId,
        scheduleIds,
        jumlahHadir: 0,
        pesan: '',
        root: null,

        sinkron(studentId, hadir) {
            const radioHadir = this.root.querySelector(`[data-status-hadir="${studentId}"]`);

            if (hadir) {
                if (radioHadir) radioHadir.checked = true;
                return;
            }

            const sudah = this.root.querySelector(`[data-status="${studentId}"]:checked`);
            if (!sudah) {
                const alpa = [...this.root.querySelectorAll(`[data-status="${studentId}"]`)].find(el => el.value === 'alpa');
                if (alpa) alpa.checked = true;
            }
        },

        hitung() {
            this.jumlahHadir = this.root.querySelectorAll('[data-hadir]:checked').length;
        },

        centangSemua() {
            this.root.querySelectorAll('[data-hadir]').forEach(el => {
                el.checked = true;
                el.dispatchEvent(new Event('change'));
            });
        },

        async salin(dari) {
            this.pesan = '';

            try {
                const res = await fetch('{{ route('admin.absensi-mapel.salin') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        tanggal: '{{ $tanggal->toDateString() }}',
                        schedule_ids: this.scheduleIds,
                        dari,
                    }),
                });

                const json = await res.json();

                if (!res.ok) {
                    this.pesan = json.pesan ?? 'Gagal menyalin data.';
                    return;
                }

                Object.entries(json.data).forEach(([studentId, isi]) => {
                    const hadir = isi.status === 'hadir';
                    const cek = this.root.querySelector(`[data-hadir="${studentId}"]`);
                    if (!cek) return;

                    if (!hadir) {
                        const target = [...this.root.querySelectorAll(`[data-status="${studentId}"]`)].find(el => el.value === isi.status);
                        if (target) target.checked = true;
                    }

                    const ket = this.root.querySelector(`[data-ket="${studentId}"]`);
                    if (ket) ket.value = isi.keterangan ?? '';

                    cek.checked = hadir;
                    cek.dispatchEvent(new Event('change'));
                });

                this.hitung();
            } catch (e) {
                this.pesan = 'Terjadi kesalahan saat memproses permintaan salin.';
            }
        },
    };
}
</script>
@endpush
@endsection
