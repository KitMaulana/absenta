@if (session('sukses'))
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900 shadow-2xs backdrop-blur">
        <div class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-emerald-600 text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="font-semibold text-emerald-950">{{ session('sukses') }}</p>
        </div>
    </div>
@endif

@if (session('gagal'))
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-sm text-rose-900 shadow-2xs backdrop-blur">
        <div class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-rose-600 text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="font-semibold text-rose-950">{{ session('gagal') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-sm text-rose-900 shadow-2xs backdrop-blur">
        <div class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-rose-600 text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-bold text-rose-950">Periksa kembali isian formulir:</p>
            <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-rose-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
