@extends('layouts.admin')
@section('title', $admin->exists ? 'Edit Admin' : 'Tambah Admin')
@section('subtitle', $admin->exists ? 'Perbarui informasi dan hak akses pengguna' : 'Buat akun pengurus baru untuk kelas')

@section('content')
<x-card class="max-w-2xl" judul="{{ $admin->exists ? 'Formulir Edit Admin' : 'Formulir Tambah Admin' }}">
    <form method="POST" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}" class="space-y-5">
        @csrf
        @if ($admin->exists) @method('PUT') @endif

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Lengkap Pengguna</label>
            <input id="name" name="name" value="{{ old('name', $admin->name) }}" required placeholder="Nama pengurus kelas"
                   class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required placeholder="email@kelas.test"
                   class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Peran / Hak Akses</label>
            <select id="role" name="role" class="w-full rounded-xl border-slate-200 text-sm font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (\App\Models\User::ROLES as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('role', $admin->role) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-[11px] text-slate-400">Hak Akses: Hanya Wali Kelas yang dapat mengelola Pengaturan Kelas dan Manajemen Admin.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                    Kata Sandi
                    @if ($admin->exists)
                        <span class="font-normal text-slate-400">(Kosongkan jika tidak diubah)</span>
                    @endif
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password" @required(! $admin->exists) placeholder="Minimal 8 karakter"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Konfirmasi Kata Sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $admin->exists) placeholder="Ulangi kata sandi"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
            <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $admin->is_active ?? true))
                       class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>Akun Aktif (Dapat masuk dan menggunakan aplikasi)</span>
            </label>
        </div>

        <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
            <button type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                Simpan Akun Admin
            </button>
            <a href="{{ route('admin.admins.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</x-card>
@endsection
