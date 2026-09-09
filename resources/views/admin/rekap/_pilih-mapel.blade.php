<div>
    <label for="mapel" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Mata Pelajaran</label>
    <select id="mapel" name="mapel" class="rounded-xl border-slate-200 text-xs font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($daftarMapel as $m)
            <option value="{{ $m->id }}" @selected($mapel?->id === $m->id)>{{ $m->nama }}</option>
        @endforeach
    </select>
</div>
