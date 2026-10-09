<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Pilihan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
        <header class="mb-8 sm:mb-10">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-amber-600">Promo</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Promo Pilihan</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">Temukan penawaran terbaru dan keuntungan dari setiap program promo.</p>
        </header>

        <div class="relative z-30 border-b border-slate-200" role="tablist" aria-label="Kategori promo">
            <div class="flex gap-6">
                <div class="relative border-b-2 {{ $activeTab === 'program' ? 'border-amber-500' : 'border-transparent' }} pb-3 transition-colors" id="promo-dropdown-wrapper">
                    <button type="button" id="promo-dropdown-btn"
                        role="tab"
                        aria-selected="{{ $activeTab === 'program' ? 'true' : 'false' }}"
                        aria-controls="program-promo-panel"
                        class="flex items-center gap-1.5 text-sm font-bold transition-colors {{ $activeTab === 'program' ? 'text-amber-600' : 'text-slate-500 hover:text-slate-700' }} focus:outline-none"
                        aria-haspopup="listbox" aria-expanded="false" aria-controls="promo-dropdown-list">
                        <span id="promo-dropdown-label">{{ $selectedCategory !== '' ? $selectedCategory : 'Program Promo' }}</span>
                        <svg id="promo-dropdown-chevron" class="h-3.5 w-3.5 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <ul id="promo-dropdown-list" role="listbox" aria-label="Pilih kategori promo"
                        class="absolute left-0 top-full z-40 mt-1 hidden min-w-[180px] overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        <li role="option" aria-selected="{{ $selectedCategory === '' ? 'true' : 'false' }}">
                            <a href="{{ route('promo.index') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors {{ $selectedCategory === '' ? 'bg-amber-50 text-amber-600' : 'text-slate-700 hover:bg-slate-50' }}">
                                @if ($selectedCategory === '')
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full"></span>
                                @endif
                                Program Promo
                            </a>
                        </li>
                        @foreach ($categories as $category)
                            <li role="option" aria-selected="{{ $selectedCategory === $category ? 'true' : 'false' }}">
                                <a href="{{ route('promo.index', ['kategori' => $category]) }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors {{ $selectedCategory === $category ? 'bg-amber-50 text-amber-600' : 'text-slate-700 hover:bg-slate-50' }}">
                                    @if ($selectedCategory === $category)
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    @else
                                        <span class="h-1.5 w-1.5 rounded-full"></span>
                                    @endif
                                    {{ $category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative border-b-2 {{ $activeTab === 'pamflet' ? 'border-amber-500' : 'border-transparent' }} pb-3 transition-colors" id="pamflet-tab-wrapper">
                    <button type="button"
                        id="pamflet-tab-btn"
                        role="tab"
                        aria-selected="{{ $activeTab === 'pamflet' ? 'true' : 'false' }}"
                        aria-controls="pamflet-promo-panel"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        aria-controls="pamflet-dropdown-list"
                        class="flex items-center gap-1.5 text-sm font-bold transition-colors {{ $activeTab === 'pamflet' ? 'text-amber-600' : 'text-slate-500 hover:text-slate-700' }} focus:outline-none">
                        <span id="pamflet-tab-label">{{ $selectedPamfletCategory !== '' ? $selectedPamfletCategory : 'Pamflet & Desain Promo' }}</span>
                        <svg id="pamflet-dropdown-chevron" class="h-3.5 w-3.5 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <ul id="pamflet-dropdown-list" role="listbox" aria-label="Pilih kategori pamflet"
                        class="absolute left-0 top-full z-40 mt-1 hidden min-w-[220px] overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        <li role="option" aria-selected="{{ $selectedPamfletCategory === '' ? 'true' : 'false' }}">
                            <a href="{{ route('promo.index', ['tab' => 'pamflet']) }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors {{ $selectedPamfletCategory === '' ? 'bg-amber-50 text-amber-600' : 'text-slate-700 hover:bg-slate-50' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $selectedPamfletCategory === '' ? 'bg-amber-500' : '' }}"></span>
                                Semua Pamflet
                            </a>
                        </li>
                        @foreach ($pamfletCategories as $category)
                            <li role="option" aria-selected="{{ $selectedPamfletCategory === $category ? 'true' : 'false' }}">
                                <a href="{{ route('promo.index', ['tab' => 'pamflet', 'pamflet_kategori' => $category]) }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold transition-colors {{ $selectedPamfletCategory === $category ? 'bg-amber-50 text-amber-600' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $selectedPamfletCategory === $category ? 'bg-amber-500' : '' }}"></span>
                                    {{ $category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <section id="program-promo-panel" class="pt-6 sm:pt-8 {{ $activeTab === 'program' ? '' : 'hidden' }}" role="tabpanel">

            <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:block">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th class="w-14 px-5 py-4 text-center">#</th>
                                <th class="px-5 py-4">Program</th>
                                <th class="w-32 px-5 py-4 text-center">Varian</th>
                                <th class="px-5 py-4">Benefit</th>
                                <th class="px-5 py-4">Rules</th>
                                <th class="w-32 px-5 py-4 text-right">Harga</th>
                                <th class="w-40 px-5 py-4 text-center">Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($promos->groupBy(fn ($promo) => $promo->kategori ?: 'Lainnya') as $kategori => $groupedPromos)
                                <tr class="border-y border-slate-100 bg-slate-50">
                                    <td colspan="7" class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $kategori }}</td>
                                </tr>
                                @foreach ($groupedPromos as $index => $promo)
                                    <tr class="border-b border-slate-100 last:border-0">
                                        <td class="px-5 py-4 text-center text-slate-400">{{ $index + 1 }}</td>
                                        <td class="px-5 py-4 font-bold uppercase text-slate-900">{{ $promo->program ?: '-' }}</td>
                                        <td class="px-5 py-4 text-center font-semibold text-slate-600">{{ $promo->warna ?: '-' }}</td>
                                        <td class="max-w-xs whitespace-pre-line px-5 py-4 leading-6 text-slate-600">{{ $promo->benefit ?: '-' }}</td>
                                        <td class="max-w-xs whitespace-pre-line px-5 py-4 leading-6 text-slate-500">{{ $promo->rules ?: '-' }}</td>
                                        <td class="px-5 py-4 text-right font-bold text-amber-600">{{ filled($promo->harga) ? 'Rp'.number_format($promo->harga, 0, ',', '.') : '-' }}</td>
                                        <td class="px-5 py-4 text-center italic text-slate-500">{{ $promo->periode ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-16 text-center text-slate-400">Belum ada data program promo</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse ($promos->groupBy(fn ($promo) => $promo->kategori ?: 'Lainnya') as $kategori => $groupedPromos)
                    <p class="px-2 pt-2 text-xs font-bold uppercase tracking-wider text-slate-400">{{ $kategori }}</p>
                    @foreach ($groupedPromos as $promo)
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="text-sm font-bold uppercase text-slate-900">{{ $promo->program ?: '-' }}</h2>
                                @if (filled($promo->warna))
                                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">{{ $promo->warna }}</span>
                                @endif
                            </div>
                            @if (filled($promo->benefit))
                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $promo->benefit }}</p>
                            @endif
                            <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                                <span class="text-xs italic text-slate-500">{{ $promo->periode ?: '-' }}</span>
                                @if (filled($promo->harga))
                                    <span class="text-sm font-bold text-amber-600">Rp{{ number_format($promo->harga, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-400">Belum ada data program promo</div>
                @endforelse
            </div>
        </section>

        <section id="pamflet-promo-panel" class="pt-6 sm:pt-8 {{ $activeTab === 'pamflet' ? '' : 'hidden' }}" role="tabpanel">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($pamflets as $pamflet)
                    @php
                        $fileUrl = filled($pamflet->file_path) ? '/api/promo-pamflets/file/' . rawurlencode($pamflet->file_path) : null;
                    @endphp
                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        @if ($fileUrl)
                            <button type="button" class="pamflet-preview-trigger relative block aspect-[4/5] w-full overflow-hidden bg-slate-100 text-left focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-inset"
                                data-pamflet-src="{{ $fileUrl }}" data-pamflet-name="{{ $pamflet->nama }}" aria-label="Lihat pamflet {{ $pamflet->nama }} ukuran penuh">
                                <img src="{{ $fileUrl }}" alt="{{ $pamflet->nama }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                            </button>
                        @else
                            <div class="flex aspect-[4/5] w-full items-center justify-center bg-slate-100 text-slate-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col p-4 sm:p-5">
                            @if (filled($pamflet->kategori))
                                <p class="mb-1 text-xs font-bold uppercase tracking-wider text-amber-600">{{ $pamflet->kategori }}</p>
                            @endif
                            @if ($fileUrl)
                                <button type="button" class="pamflet-preview-trigger text-left focus:outline-none group/title" data-pamflet-src="{{ $fileUrl }}" data-pamflet-name="{{ $pamflet->nama }}">
                                    <h2 class="text-base font-bold text-slate-900 group-hover/title:text-amber-600 transition-colors">{{ $pamflet->nama }}</h2>
                                </button>
                            @else
                                <h2 class="text-base font-bold text-slate-900">{{ $pamflet->nama }}</h2>
                            @endif
                            @if (filled($pamflet->deskripsi))
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $pamflet->deskripsi }}</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-400">
                        Belum ada data pamflet promo
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <div id="pamflet-preview-modal" class="fixed inset-0 hidden items-center justify-center bg-slate-950 p-2 sm:p-4" style="z-index: 99999;" role="dialog" aria-modal="true" aria-labelledby="pamflet-preview-title">
        <button type="button" id="pamflet-preview-backdrop" class="absolute inset-0 cursor-default" aria-label="Tutup preview pamflet"></button>
        <div class="relative z-10 flex h-full w-full max-h-[100dvh] max-w-[100dvw] items-center justify-center p-2">
            <button type="button" id="pamflet-preview-close" class="absolute top-3 right-3 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-slate-800/80 text-white shadow-xl backdrop-blur-md transition hover:bg-slate-700 hover:scale-105 active:scale-95" aria-label="Tutup preview pamflet">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
            <img id="pamflet-preview-image" src="" alt="" class="max-h-[92dvh] max-w-[95dvw] w-auto h-auto rounded-lg object-contain shadow-2xl transition-all">
            <p id="pamflet-preview-title" class="sr-only"></p>
        </div>
    </div>

    <script>
        (function () {
            const promoWrapper = document.getElementById('promo-dropdown-wrapper');
            const promoBtn = document.getElementById('promo-dropdown-btn');
            const promoList = document.getElementById('promo-dropdown-list');
            const promoChevron = document.getElementById('promo-dropdown-chevron');

            const pamfletWrapper = document.getElementById('pamflet-tab-wrapper');
            const pamfletBtn = document.getElementById('pamflet-tab-btn');
            const pamfletList = document.getElementById('pamflet-dropdown-list');
            const pamfletChevron = document.getElementById('pamflet-dropdown-chevron');

            const programPanel = document.getElementById('program-promo-panel');
            const pamfletPanel = document.getElementById('pamflet-promo-panel');
            const pamfletPreviewModal = document.getElementById('pamflet-preview-modal');
            const pamfletPreviewImage = document.getElementById('pamflet-preview-image');
            const pamfletPreviewTitle = document.getElementById('pamflet-preview-title');
            const pamfletPreviewClose = document.getElementById('pamflet-preview-close');
            const pamfletPreviewBackdrop = document.getElementById('pamflet-preview-backdrop');

            function openPamfletPreview(trigger) {
                if (!pamfletPreviewModal || !pamfletPreviewImage) return;
                const source = trigger.dataset.pamfletSrc || '';
                const name = trigger.dataset.pamfletName || 'Preview pamflet';
                pamfletPreviewImage.src = source;
                pamfletPreviewImage.alt = name;
                if (pamfletPreviewTitle) pamfletPreviewTitle.textContent = name;
                pamfletPreviewModal.classList.remove('hidden');
                pamfletPreviewModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
                if (pamfletPreviewClose) pamfletPreviewClose.focus();
            }

            function closePamfletPreview() {
                if (!pamfletPreviewModal || !pamfletPreviewImage) return;
                pamfletPreviewModal.classList.add('hidden');
                pamfletPreviewModal.classList.remove('flex');
                pamfletPreviewImage.src = '';
                document.body.classList.remove('overflow-hidden');
            }

            function openDropdown() {
                if (promoList) {
                    promoList.classList.remove('hidden');
                    if (promoChevron) promoChevron.style.transform = 'rotate(180deg)';
                    if (promoBtn) promoBtn.setAttribute('aria-expanded', 'true');
                }
            }

            function closeDropdown() {
                if (promoList) {
                    promoList.classList.add('hidden');
                    if (promoChevron) promoChevron.style.transform = '';
                    if (promoBtn) promoBtn.setAttribute('aria-expanded', 'false');
                }
            }

            function openPamfletDropdown() {
                if (pamfletList) {
                    pamfletList.classList.remove('hidden');
                    if (pamfletChevron) pamfletChevron.style.transform = 'rotate(180deg)';
                    if (pamfletBtn) pamfletBtn.setAttribute('aria-expanded', 'true');
                }
            }

            function closePamfletDropdown() {
                if (pamfletList) {
                    pamfletList.classList.add('hidden');
                    if (pamfletChevron) pamfletChevron.style.transform = '';
                    if (pamfletBtn) pamfletBtn.setAttribute('aria-expanded', 'false');
                }
            }

            function activateProgram(updateHistory) {
                closePamfletDropdown();
                if (promoWrapper) {
                    promoWrapper.classList.remove('border-transparent');
                    promoWrapper.classList.add('border-amber-500');
                }
                if (promoBtn) {
                    promoBtn.classList.remove('text-slate-500', 'hover:text-slate-700');
                    promoBtn.classList.add('text-amber-600');
                    promoBtn.setAttribute('aria-selected', 'true');
                }
                if (pamfletWrapper) {
                    pamfletWrapper.classList.remove('border-amber-500');
                    pamfletWrapper.classList.add('border-transparent');
                }
                if (pamfletBtn) {
                    pamfletBtn.classList.remove('text-amber-600');
                    pamfletBtn.classList.add('text-slate-500', 'hover:text-slate-700');
                    pamfletBtn.setAttribute('aria-selected', 'false');
                }
                if (programPanel) programPanel.classList.remove('hidden');
                if (pamfletPanel) pamfletPanel.classList.add('hidden');

                if (updateHistory) {
                    try {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('tab');
                        window.history.pushState({}, '', url.toString());
                    } catch (e) {}
                }
            }

            function activatePamflet(updateHistory) {
                closeDropdown();
                if (pamfletWrapper) {
                    pamfletWrapper.classList.remove('border-transparent');
                    pamfletWrapper.classList.add('border-amber-500');
                }
                if (pamfletBtn) {
                    pamfletBtn.classList.remove('text-slate-500', 'hover:text-slate-700');
                    pamfletBtn.classList.add('text-amber-600');
                    pamfletBtn.setAttribute('aria-selected', 'true');
                }
                if (promoWrapper) {
                    promoWrapper.classList.remove('border-amber-500');
                    promoWrapper.classList.add('border-transparent');
                }
                if (promoBtn) {
                    promoBtn.classList.remove('text-amber-600');
                    promoBtn.classList.add('text-slate-500', 'hover:text-slate-700');
                    promoBtn.setAttribute('aria-selected', 'false');
                }
                if (programPanel) programPanel.classList.add('hidden');
                if (pamfletPanel) pamfletPanel.classList.remove('hidden');

                if (updateHistory) {
                    try {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', 'pamflet');
                        window.history.pushState({}, '', url.toString());
                    } catch (e) {}
                }
            }

            if (promoBtn) {
                promoBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (pamfletPanel && !pamfletPanel.classList.contains('hidden')) {
                        activateProgram(true);
                        openDropdown();
                        return;
                    }
                    if (promoList) {
                        promoList.classList.contains('hidden') ? openDropdown() : closeDropdown();
                    }
                });
            }

            if (pamfletBtn) {
                pamfletBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (programPanel && !programPanel.classList.contains('hidden')) {
                        activatePamflet(true);
                        openPamfletDropdown();
                        return;
                    }
                    if (pamfletList) {
                        pamfletList.classList.contains('hidden') ? openPamfletDropdown() : closePamfletDropdown();
                    }
                });
            }

            document.querySelectorAll('.pamflet-preview-trigger').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    openPamfletPreview(trigger);
                });
            });

            if (pamfletPreviewClose) pamfletPreviewClose.addEventListener('click', closePamfletPreview);
            if (pamfletPreviewBackdrop) pamfletPreviewBackdrop.addEventListener('click', closePamfletPreview);

            window.addEventListener('popstate', function () {
                const params = new URLSearchParams(window.location.search);
                if (params.get('tab') === 'pamflet') {
                    activatePamflet(false);
                } else {
                    activateProgram(false);
                }
            });

            document.addEventListener('click', function (e) {
                if (promoWrapper && !promoWrapper.contains(e.target)) {
                    closeDropdown();
                }
                if (pamfletWrapper && !pamfletWrapper.contains(e.target)) {
                    closePamfletDropdown();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    closePamfletDropdown();
                    closePamfletPreview();
                }
            });
        })();
    </script>
</body>
</html>
