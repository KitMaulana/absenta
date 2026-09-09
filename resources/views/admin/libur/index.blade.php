@extends('layouts.admin')
@section('title', 'Hari Libur')
@section('subtitle', 'Daftar tanggal libur &middot; Tidak dihitung sebagai hari efektif presensi')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    {{-- Form Tambah Hari Libur --}}
    <x-card judul="Tambah Hari Libur">
        <form method="POST" action="{{ route('admin.libur.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="tanggal" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Libur</label>
                <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', now()->toDateString()) }}" required
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="keterangan" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Keterangan Hari Libur</label>
                <input id="keterangan" name="keterangan" value="{{ old('keterangan') }}" required placeholder="Contoh: Libur Hari Raya Idul Fitri"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                + Daftarkan Hari Libur
            </button>
        </form>

        <p class="mt-4 border-t border-slate-100 pt-3 text-[11px] text-slate-400 leading-relaxed">
            Catatan: Hari libur akhir pekan (Sabtu/Minggu) tidak perlu didaftarkan di sini. Cukup atur checklist hari aktif di menu <strong>Pengaturan Kelas</strong>.
        </p>
    </x-card>

    {{-- Daftar Hari Libur Tercatat --}}
    <x-card class="lg:col-span-2" padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Daftar Hari Libur</span>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $libur->total() }} Hari</span>
            </div>
        </x-slot:judul>

        @if ($libur->isEmpty())
            <x-kosong pesan="Belum ada catatan hari libur." ikon="🏖️" />
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($libur as $l)
                    <form id="hapus-libur-{{ $l->id }}" method="POST" action="{{ route('admin.libur.destroy', $l) }}"
                          onsubmit="return confirm('Hapus hari libur ini?')" class="hidden">
                        @csrf @method('DELETE')
                    </form>

                    <div class="flex flex-wrap items-end gap-3 p-4 hover:bg-slate-50/50 transition">
                        <form method="POST" action="{{ route('admin.libur.update', $l) }}" class="flex flex-1 flex-wrap items-end gap-3">
                            @csrf @method('PUT')
                            <div class="w-44">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal</label>
                                <input name="tanggal" type="date" value="{{ $l->tanggal->toDateString() }}"
                                       class="w-full rounded-xl border-slate-200 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-[11px] font-medium text-indigo-600">{{ \App\Support\Tanggal::namaHari($l->tanggal) }}</p>
                            </div>
                            <div class="min-w-[12rem] flex-1">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan Libur</label>
                                <input name="keterangan" value="{{ $l->keterangan }}"
                                       class="w-full rounded-xl border-slate-200 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                Simpan
                            </button>
                        </form>

                        <button type="submit" form="hapus-libur-{{ $l->id }}"
                                class="rounded-xl border border-rose-200 bg-white px-3.5 py-2 text-xs font-bold text-rose-700 shadow-2xs hover:bg-rose-50 transition">
                            Hapus
                        </button>
                    </div>
                @endforeach
            </div>

            @if ($libur->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/40 px-5 py-3 rounded-b-2xl">{{ $libur->links() }}</div>
            @endif
        @endif
    </x-card>
</div>
@endsection
