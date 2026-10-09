@verbatim
<div id="app" class="min-h-[100dvh]" v-cloak>
    <transition name="fade">
        <div v-if="appLoading" class="app-loading-screen">
            <div class="app-loading-card">
                <img src="/asset/images/logo.png" alt="Pura Pura Ponsel" class="app-loading-logo" />
                <div class="app-loading-title">Marketing Dashboard</div>
                <div class="app-loading-spinner"></div>
            </div>
        </div>
    </transition>

    <div v-if="runtimeError"
        class="fixed top-3 left-1/2 -translate-x-1/2 z-[10000] w-[94%] max-w-3xl bg-danger border border-danger text-light rounded-2xl px-4 py-3">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <div class="min-w-0 flex-1">
                <div class="text-body-sm font-medium uppercase">Sistem Error</div>
                <div class="text-body leading-relaxed break-words mt-0.5">{{ runtimeError }}</div>
            </div>
            <button @click="runtimeError = null" class="text-danger hover:text-danger">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <transition name="toast">
        <div v-if="notification && notification.open"
            :class="['fixed bottom-6 inset-x-0 mx-auto z-[10000] w-fit px-3 py-1.5 rounded-xl text-body-sm border flex items-center gap-2 min-w-[200px] max-w-[min(92vw,480px)] overflow-hidden', notification.type === 'error' ? 'bg-danger text-light border-danger' : notification.type === 'warning' ? 'bg-amber text-light border-amber' : 'bg-success text-light border-success']">
            <div
                :class="['w-6 h-6 rounded-lg flex items-center justify-center shrink-0', notification.type === 'error' ? 'bg-danger text-light' : notification.type === 'warning' ? 'bg-amber text-light' : 'bg-success text-light']">
                <i :class="['fa-solid text-[10px]', notification.icon]"></i>
            </div>
            <div class="min-w-0 flex items-center gap-1.5 overflow-hidden whitespace-nowrap leading-none">
                <div class="text-overline font-bold uppercase shrink-0">
                    {{ notification.type === 'error' ? 'Error' : notification.type === 'warning' ? 'Perhatian' : 'Berhasil' }}
                </div>
                <div class="min-w-0 truncate leading-none text-body-sm">{{ notification.message }}</div>
            </div>
        </div>
    </transition>

    <transition name="fade">
        <div v-if="!currentUser && !appLoading" class="min-h-[100dvh] bg-slate-950 overscroll-none">
            <div class="grid min-h-[100dvh] lg:grid-cols-[1.1fr_0.9fr]">
                <div class="relative hidden overflow-hidden lg:block">
                    <div class="brand-gradient absolute inset-0"></div>
                    <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
@endverbatim
                    @include('dashboard.partials.shell.waves', ['fill1' => 'rgba(255,255,255,.10)', 'fill2' => 'rgba(255,255,255,.15)'])
@verbatim
                    <div class="relative flex h-full flex-col justify-between p-12 text-white">
                        <div>
                            <div class="inline-flex items-center gap-3 rounded-full border border-white/40 bg-white/70 px-4 py-2 text-sm font-semibold text-[var(--ppp-accent)] backdrop-blur-sm">
                                <img src="/asset/images/logo.png" alt="Pura Pura Ponsel" class="h-7 w-7 object-contain" />
                                <span>PURA PURA PONSEL</span>
                            </div>
                        </div>
                        <div class="max-w-xl">
                            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-white/70">Marketing Dashboard</div>
                            <h1 class="mt-4 text-4xl font-black leading-tight">Satu pusat kendali konten, kampanye, dan performa marketing.</h1>
                            <p class="mt-4 text-base leading-7 text-white/80">Pantau master plan, distribusi, analitik, intelijen pasar, dan layanan pelanggan dalam satu ruang kerja terpadu.</p>
                        </div>
                        <div class="text-xs text-white/60">&copy; {{ new Date().getFullYear() }} Pura Pura Ponsel. All rights reserved.</div>
                    </div>
                </div>

                <div class="relative flex min-h-[100dvh] bg-white md:items-center md:justify-center md:overflow-hidden md:bg-white md:px-6 md:py-6 lg:bg-white lg:px-10">
                    <div class="relative w-full md:max-w-[720px] lg:max-w-[340px] [--field-height:32px] [--field-radius:12px] [--field-font-size:11px] [--field-padding-y:7px]">
                        <div class="flex min-h-[100dvh] flex-col md:grid md:grid-cols-[1.1fr_0.9fr] md:gap-0 md:rounded-[28px] md:border md:border-white/40 md:bg-white md:p-0 md:min-h-[480px] md:overflow-hidden lg:block lg:min-h-0 lg:overflow-visible lg:rounded-none lg:border-0 lg:bg-transparent lg:px-0 lg:py-0 lg:shadow-none">
                            <div class="relative overflow-hidden md:hidden" style="padding-top: env(safe-area-inset-top)">
                                <div class="brand-gradient absolute inset-0"></div>
                                <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(rgba(255,255,255,0.9) 1px, transparent 1px); background-size: 14px 14px;"></div>
                                <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/10 blur-[2px]"></div>
                                <div class="pointer-events-none absolute -left-6 top-8 h-20 w-20 rounded-full bg-white/10"></div>
@endverbatim
                                @include('dashboard.partials.shell.waves', ['fill1' => 'rgba(255,255,255,.10)', 'fill2' => 'rgba(255,255,255,.15)', 'height1' => 'h-16', 'height2' => 'h-10'])
@verbatim
                                <div class="relative flex items-center gap-2 px-5 pt-7">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-white/25 bg-white/15 backdrop-blur-md">
                                        <img src="/asset/images/logo.png" alt="Pura Pura Ponsel" class="h-5 w-5 object-contain" />
                                    </span>
                                    <span class="text-[11px] font-bold tracking-wide text-white/90">PURA PURA PONSEL</span>
                                </div>
                                <div class="relative px-5 pb-14 pt-6">
                                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/70">Marketing Dashboard</div>
                                    <h2 class="mt-2 text-[22px] font-black leading-tight text-white">Satu pusat kendali konten, kampanye, dan performa marketing.</h2>
                                    <p class="mt-2 text-[13px] leading-5 text-white/85">Pantau master plan, distribusi, analitik, dan insight tim dari satu dashboard.</p>
                                </div>
                            </div>

                            <div class="hidden md:relative md:flex md:flex-col md:justify-between md:overflow-hidden md:p-8 lg:hidden">
                                <div class="brand-gradient absolute inset-0"></div>
                                <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10"></div>
@endverbatim
                                @include('dashboard.partials.shell.waves', ['fill1' => 'rgba(255,255,255,.10)', 'fill2' => 'rgba(255,255,255,.15)', 'height1' => 'h-24', 'height2' => 'h-16'])
@verbatim
                                <div class="relative z-10 flex h-full flex-col justify-between text-white">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full border border-white/40 bg-white/70 px-3 py-1.5 text-xs font-semibold text-[var(--ppp-accent)] backdrop-blur-sm">
                                            <img src="/asset/images/logo.png" alt="Pura Pura Ponsel" class="h-5 w-5 object-contain" />
                                            <span>PURA PURA PONSEL</span>
                                        </div>
                                    </div>
                                    <div class="max-w-md">
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Marketing Dashboard</div>
                                        <h1 class="mt-3 text-xl font-black leading-tight">Satu pusat kendali konten, kampanye, dan performa marketing.</h1>
                                        <p class="mt-3 text-xs leading-5 text-white/80">Pantau master plan, distribusi, analitik, dan insight tim dari satu dashboard.</p>
                                    </div>
                                    <div class="text-[10px] text-white/60">&copy; {{ new Date().getFullYear() }} Pura Pura Ponsel. All rights reserved.</div>
                                </div>
                            </div>

                            <div class="md:flex md:flex-col md:justify-center md:p-8 lg:block lg:p-0">
                                <div class="auth-sheet relative -mt-7 rounded-t-[32px] bg-white px-5 pb-2 pt-5 md:contents md:rounded-none">
                                    <div class="mx-auto mb-4 h-1 w-9 rounded-full bg-slate-200 md:hidden"></div>

                                    <div class="mb-6">
                                        <span class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl text-white" style="background: linear-gradient(135deg, #ffb833, var(--ppp-accent) 60%, #c2410c);">
                                            <i class="fa-solid fa-lock text-[15px]"></i>
                                        </span>
                                        <div class="text-2xl font-black tracking-tight text-slate-900">Masuk</div>
                                        <div class="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Marketing Dashboard Internal</div>
                                    </div>

                                    <form class="space-y-4" @submit.prevent="handleLogin">
                                        <div>
                                            <label for="login-username" class="field-label">Username</label>
                                            <div class="mt-1.5">
                                                <input id="login-username" name="username" v-model="loginForm.username" type="text" placeholder="Masukkan username"
                                                    autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" inputmode="text"
                                                    class="field-input" required autofocus />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="login-pin" class="field-label">Password</label>
                                            <div class="relative mt-1.5">
                                                <input id="login-pin" name="pin" v-model="loginForm.pin" :type="showPin ? 'text' : 'password'" placeholder="Masukkan password"
                                                    autocomplete="current-password" autocapitalize="none" autocorrect="off" spellcheck="false"
                                                    class="field-input pr-12" required @keyup.enter="handleLogin" />
                                                <button type="button" @click="showPin = !showPin" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-[var(--ppp-accent)]" :aria-label="showPin ? 'Sembunyikan password' : 'Tampilkan password'">
                                                    <i :class="showPin ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-[13px]"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 pt-1">
                                            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-medium text-slate-500">
                                                <span class="auth-checkbox-wrap">
                                                    <input type="checkbox" v-model="rememberUsername" @change="handleRememberUsernameChange" class="auth-remember-checkbox peer" />
                                                    <span class="auth-checkbox-indicator">
                                                        <i class="fa-solid fa-check text-[9px]"></i>
                                                    </span>
                                                </span>
                                                <span>Ingat saya</span>
                                            </label>
                                        </div>

                                        <div class="pt-1">
                                            <button type="submit" :disabled="submitting" :class="{ 'opacity-50': submitting }" class="btn btn-primary auth-submit flex w-full items-center justify-center gap-2">
                                                <i v-if="submitting" class="fa-solid fa-spinner animate-spin text-[12px]"></i>
                                                <span>{{ submitting ? 'Sedang masuk...' : 'Masuk' }}</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="relative mt-auto overflow-hidden pt-10 md:hidden">
@endverbatim
                                @include('dashboard.partials.shell.waves', ['fill1' => 'rgba(255,165,0,.08)', 'fill2' => 'rgba(255,165,0,.10)', 'height1' => 'h-20', 'height2' => 'h-12'])
@verbatim
                                <div class="relative px-4 text-center text-[10px] text-slate-400" style="padding-bottom: max(24px, env(safe-area-inset-bottom))">&copy; {{ new Date().getFullYear() }} Pura Pura Ponsel. All rights reserved.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <div v-if="currentUser && !appLoading" class="min-h-[100dvh] bg-slate-50">
        <div data-sidebar-backdrop @click="sidebarOpen ? closeSidebar() : null"
            :class="['fixed inset-0 z-[70] glass-backdrop lg:hidden transition-opacity duration-300 ease-out', sidebarOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none']">
        </div>
@endverbatim
        @include('dashboard.partials.shell.chat-panel')
        @include('dashboard.partials.shell.app-frame-sidebar')
@verbatim
        <div
            class="dashboard-main-shell min-h-[100dvh]"
            :style="isMobileViewport ? null : { paddingLeft: sidebarCollapsed ? '0px' : '15rem' }">
@endverbatim
            @include('dashboard.partials.shell.app-frame-header')
@verbatim

            <main class="p-4 pb-6 md:p-6 space-y-4">
                <div class="flex items-center gap-2 text-body text-slate-400">
                    <template v-for="(item, idx) in breadcrumbItems" :key="idx">
                        <span :class="idx === breadcrumbItems.length - 1 ? 'text-ppp-nav-text font-medium' : ''">{{ item }}</span>
                        <i v-if="idx < breadcrumbItems.length - 1"
                            class="fa-solid fa-chevron-right text-overline-xs opacity-50"></i>
                    </template>
                </div>
@endverbatim
                {{-- Satu menu = satu halaman: halaman dengan URL sendiri hanya merender menunya. $legacyMenus menyusut tiap batch migrasi (docs/hash-to-url-routing-plan.md). --}}
                @php
                    $legacyMenus = [
                    'dashboard.partials.menus.dashboard',
                    ];
                @endphp
                @php
                    // Menu yang sudah punya URL sendiri; hanya tampil di `/` bila flag dashboard.url_routing dimatikan (rollback ke hash).
                    $migratedMenus = [
                        // Sudah punya URL sejak sebelum migrasi hash
                        'dashboard.partials.menus.promo-pamflet',
                        'dashboard.partials.menus.pricelist-katalog',
                        'dashboard.partials.menus.template-background',
                        'dashboard.partials.menus.apple-katalog',
                        'dashboard.partials.menus.img-repo',
                        'dashboard.partials.menus.asset-vendor-inventory',

                        'dashboard.partials.menus.harga-kompetitor',
                        'dashboard.partials.menus.laporan-event',
                        'dashboard.partials.menus.settings',
                        'dashboard.partials.menus.nama-stock',
                        'dashboard.partials.menus.auth-users',
                        'dashboard.partials.menus.activity-logs',
                        'dashboard.partials.menus.order-online',
                        'dashboard.partials.menus.unit-ditanya',
                        'dashboard.partials.menus.claim-garansi',
                        'dashboard.partials.menus.keep-barang',

                        // Batch C
                        'dashboard.partials.menus.input-claim',
                        'dashboard.partials.menus.garansi-cermati',
                        'dashboard.partials.menus.garansi-resmi',

                        // Batch D
                        'dashboard.partials.menus.program-promo',
                        'dashboard.partials.menus.sell-out',
                        'dashboard.partials.menus.ads-log',
                        'dashboard.partials.menus.budgeting',

                        // Batch E
                        'dashboard.partials.menus.meta-story',
                        'dashboard.partials.menus.meta-feed',
                        'dashboard.partials.menus.meta-followers',

                        // Batch F
                        'dashboard.partials.menus.master-plan',
                        'dashboard.partials.menus.unboxing',
                        'dashboard.partials.menus.ideation',
                        'dashboard.partials.menus.distribution',
                        'dashboard.partials.menus.analytics',
                        'dashboard.partials.menus.calendar',
                        'dashboard.partials.menus.story',

                        // Fase 4: menu tersembunyi
                        'dashboard.partials.menus.bonus-report',
                        'dashboard.partials.menus.talent-bonus',
                        'dashboard.partials.menus.editor-performance',
                        'dashboard.partials.menus.market-pasar',
                        'dashboard.partials.menus.market-intelijen-harga',
                        'dashboard.partials.menus.market-audit-harga',
                        'dashboard.partials.menus.market-eksternal',
                        'dashboard.partials.menus.market-ext-goodponsel',
                        'dashboard.partials.menus.market-ext-devstore',
                        'dashboard.partials.menus.market-ext-rumahgadget',
                        'dashboard.partials.menus.top-content',
                        'dashboard.partials.menus.low-content',
                        'dashboard.partials.menus.analisa-insight',
                        'dashboard.partials.menus.profile',
                        'dashboard.partials.menus.content-modal',
                    ];
                @endphp
                @if($dedicatedMenuView ?? null)
                    @include($dedicatedMenuView)
                    @foreach(($dedicatedExtraViews ?? []) as $dedicatedExtraView)
                        @include($dedicatedExtraView)
                    @endforeach
                @else
                    @foreach($legacyMenus as $legacyMenu)
                        @include($legacyMenu)
                    @endforeach
                    @unless(config('dashboard.url_routing'))
                        @foreach($migratedMenus as $migratedMenu)
                            @include($migratedMenu)
                        @endforeach
                    @endunless
                @endif
                @include('dashboard.partials.shell.app-frame-global-overlays')
@verbatim
            </main>
@endverbatim
@verbatim
        </div>
    </div>
</div>
@endverbatim
