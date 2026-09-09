@extends('layouts.admin')
@section('title', 'Data Siswa')
@section('subtitle', $jumlahAktif.' siswa aktif &middot; '.$jumlahNonaktif.' nonaktif')

@section('content')
<div class="space-y-4">
    <x-card padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Daftar Siswa Kelas</span>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $jumlahAktif }} Aktif</span>
            </div>
        </x-slot:judul>
        <x-slot:aksi>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.siswa.export') }}"
                   class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('admin.siswa.import.form') }}"
                   class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <span>Import CSV</span>
                </a>
                <a href="{{ route('admin.siswa.create') }}"
                   class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Siswa</span>
                </a>
            </div>
        </x-slot:aksi>

        {{-- Filter & Search Form --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 bg-slate-50/40 p-4">
            <div class="min-w-[14rem] flex-1">
                <label for="cari" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Cari Nama / NISN</label>
                <div class="relative">
                    <input id="cari" name="cari" value="{{ request('cari') }}" placeholder="Ketik nama atau nomor induk siswa…"
                           class="w-full rounded-xl border-slate-200 text-xs shadow-2xs placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Status Keaktifan</label>
                <select id="status" name="status" class="rounded-xl border-slate-200 text-xs font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="aktif" @selected(request('status', 'aktif') === 'aktif')>Hanya Siswa Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Hanya Siswa Nonaktif</option>
                    <option value="semua" @selected(request('status') === 'semua')>Semua Status</option>
                </select>
            </div>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
                Terapkan Filter
            </button>
            @if (request()->hasAny(['cari', 'status']))
                <a href="{{ route('admin.siswa.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Reset
                </a>
            @endif
        </form>

        @if ($siswa->isEmpty())
            <x-kosong pesan="Belum ada siswa yang cocok dengan kriteria pencarian ini." ikon="🧑‍🎓" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                            <th class="px-5 py-3 font-bold">Nama Lengkap Siswa</th>
                            <th class="px-4 py-3 font-bold">NISN</th>
                            <th class="px-3 py-3 text-center font-bold">Gender</th>
                            <th class="px-4 py-3 font-bold">Kontak Orang Tua</th>
                            <th class="px-3 py-3 text-center font-bold">Status</th>
                            <th class="px-5 py-3 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($siswa as $s)
                            <tr class="hover:bg-slate-50/70 transition {{ $s->is_active ? '' : 'bg-slate-50/40 opacity-70' }}">
                                <td class="px-4 py-3 text-center tabular-nums font-bold text-slate-400">{{ $s->no_absen }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-700 shrink-0">
                                            {{ $s->no_absen }}
                                        </div>
                                        <span class="truncate {{ $s->is_active ? 'text-slate-900' : 'text-slate-500 line-through' }}">{{ $s->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-slate-600 font-medium">{{ $s->nisn ?: '—' }}</td>
                                <td class="px-3 py-3 text-center font-semibold text-slate-600">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-md {{ $s->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                        {{ $s->jenis_kelamin }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 tabular-nums text-slate-600">{{ $s->no_hp_ortu ?: '—' }}</td>
                                <td class="px-3 py-3 text-center">
                                    @if ($s->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200/60">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-500 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <a href="{{ route('admin.siswa.edit', $s) }}"
                                           class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.siswa.toggle', $s) }}">
                                            @csrf @method('PATCH')
                                            <button class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                                {{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.siswa.destroy', $s) }}"
                                              onsubmit="return confirm('Hapus data siswa ini? Tindakan hanya berhasil jika siswa belum memiliki riwayat presensi.')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 bg-white px-2.5 py-1 text-xs font-bold text-rose-700 shadow-2xs hover:bg-rose-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($siswa->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/40 px-5 py-3 rounded-b-2xl">{{ $siswa->links() }}</div>
            @endif
        @endif
    </x-card>

    <p class="text-xs text-slate-400">
        Tips: Siswa yang pindah atau lulus disarankan untuk <strong>dinonaktifkan</strong> alih-alih dihapus, agar arsip riwayat presensinya tetap tersimpan secara akurat pada laporan periode lampau.
    </p>
</div>
@endsection
