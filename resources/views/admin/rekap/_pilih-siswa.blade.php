<div>
    <label for="siswa" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Siswa</label>
    <select id="siswa" name="siswa" class="rounded-xl border-slate-200 text-xs font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($daftarSiswa as $s)
            <option value="{{ $s->id }}" @selected($siswa?->id === $s->id)>{{ $s->no_absen }}. {{ $s->nama }}</option>
        @endforeach
    </select>
</div>
