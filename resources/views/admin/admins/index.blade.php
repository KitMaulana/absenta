@extends('layouts.admin')
@section('title', 'Manajemen Admin')
@section('subtitle', 'Kelola hak akses wali kelas, ketua kelas, dan sekretaris kelas')

@section('content')
<div class="space-y-4">
    <x-card padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Daftar Administrator Kelas</span>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $admins->count() }} Akun</span>
            </div>
        </x-slot:judul>
        <x-slot:aksi>
            <a href="{{ route('admin.admins.create') }}"
               class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Admin</span>
            </a>
        </x-slot:aksi>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-bold">Nama Pengguna</th>
                        <th class="px-5 py-3 font-bold">Alamat Email</th>
                        <th class="px-4 py-3 font-bold">Peran Akses</th>
                        <th class="px-3 py-3 text-center font-bold">Status Akun</th>
                        <th class="px-5 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($admins as $admin)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 text-xs font-bold text-slate-700">
                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="truncate">{{ $admin->name }}</span>
                                        @if ($admin->id === auth()->id())
                                            <span class="ml-1.5 inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-200/60">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600 font-medium">{{ $admin->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700 border border-slate-200/60">
                                    {{ $admin->roleLabel() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if ($admin->is_active)
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
                                    <a href="{{ route('admin.admins.edit', $admin) }}"
                                       class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.admins.reset', $admin) }}"
                                          onsubmit="return confirm('Reset kata sandi admin ini? Kata sandi baru akan ditampilkan sekali saja pada layar.')">
                                        @csrf
                                        <button class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            Reset Sandi
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.admins.toggle', $admin) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                                          onsubmit="return confirm('Hapus akun admin ini secara permanen?')">
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
    </x-card>

    <p class="text-xs text-slate-400">
        Keamanan: Sistem secara otomatis memproteksi agar minimal satu akun wali kelas aktif selalu tersedia, mencegah aplikasi terkunci tanpa super admin.
    </p>
</div>
@endsection
