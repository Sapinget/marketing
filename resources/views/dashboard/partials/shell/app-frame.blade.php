@verbatim
<div id="app" class="min-h-[100dvh]" v-cloak>
    <transition name="fade">
        <div v-if="appLoading"
            class="fixed inset-0 z-[9999] bg-white flex flex-col items-center justify-center gap-6">
            <div
                class="loading-logo w-14 h-14 bg-white flex items-center justify-center p-2 border border-amber radius-panel">
                <img src="/asset/images/logo.png"
                    class="w-full h-full object-contain" alt="Logo" />
            </div>
            <div class="text-center">
                <div class="text-body font-medium text-ppp-nav-text uppercase">Pura Pura Ponsel
                </div>
                <div class="text-body-sm text-slate-400 uppercase mt-2">Menyiapkan Dashboard...</div>
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
        <div v-if="!currentUser && !appLoading"
            class="min-h-[100dvh] bg-white flex items-center justify-center p-4">
            <div class="w-full max-w-[320px] text-center">
                <img src="/asset/images/logo.png" class="w-16 h-16 object-contain mx-auto mb-5"
                    alt="Logo" />
                <h1 class="text-xl font-semibold text-slate-900 mb-1">Selamat Datang</h1>
                <p class="text-body-sm text-slate-400 mb-5 uppercase">Login untuk membuka dashboard
                </p>

                <form class="space-y-3 mb-5" @submit.prevent="handleLogin">
                    <div class="relative">
                        <label for="login-username" class="sr-only">Username</label>
                        <i
                            class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                        <input id="login-username" name="username" v-model="loginForm.username" type="text" placeholder="Username"
                            autocomplete="username"
                            class="form-input-auth" />
                    </div>
                    <div class="relative">
                        <label for="login-pin" class="sr-only">PIN Akses</label>
                        <i
                            class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                        <input id="login-pin" name="pin" v-model="loginForm.pin" type="password" placeholder="PIN Akses"
                            autocomplete="current-password"
                            class="form-input-auth" @keyup.enter="handleLogin" />
                    </div>
                </form>

                <button @click="handleLogin" :disabled="submitting"
                    class="w-full h-9 bg-slate-900 text-white rounded-xl text-body-sm font-bold uppercase hover:bg-black transition-all disabled:opacity-50">{{ submitting ? 'Mengecek...' : 'Masuk Ke Sistem' }}</button>
            </div>
        </div>
    </transition>

    <div v-if="currentUser && !appLoading" class="min-h-[100dvh] bg-slate-50">
        <div data-sidebar-backdrop @click="isSidebarOpen ? closeSidebar() : null"
            :class="['fixed inset-0 z-[70] glass-backdrop md:hidden transition-opacity duration-300 ease-out', isSidebarOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none']">
        </div>
@endverbatim
        @include('dashboard.partials.shell.chat-panel')
        @include('dashboard.partials.shell.app-frame-sidebar')
@verbatim
        <div
            class="dashboard-main-shell min-h-[100dvh]"
            :style="isMobileViewport ? null : { paddingLeft: isSidebarOpen ? '15rem' : '0px' }">
@endverbatim
            @include('dashboard.partials.shell.app-frame-header')
@verbatim

            <main class="px-3 py-3 md:px-6 md:py-5 space-y-4">
                <div class="flex items-center gap-2 text-body text-slate-400">
                    <template v-for="(item, idx) in breadcrumbItems" :key="idx">
                        <span :class="idx === breadcrumbItems.length - 1 ? 'text-ppp-nav-text font-medium' : ''">{{ item }}</span>
                        <i v-if="idx < breadcrumbItems.length - 1"
                            class="fa-solid fa-chevron-right text-overline-xs opacity-50"></i>
                    </template>
                </div>
@endverbatim
                @include('dashboard.partials.menus.dashboard')
                @include('dashboard.partials.menus.master-plan')
                @include('dashboard.partials.menus.ideation')
                @include('dashboard.partials.menus.distribution')
                @include('dashboard.partials.menus.analytics')
                @include('dashboard.partials.menus.calendar')
                @include('dashboard.partials.menus.story')
                @include('dashboard.partials.menus.analisa-insight')
                @include('dashboard.partials.menus.meta-story')
                @include('dashboard.partials.menus.meta-feed')
                @include('dashboard.partials.menus.meta-followers')
                @include('dashboard.partials.menus.unboxing')
                @include('dashboard.partials.menus.top-content')
                @include('dashboard.partials.menus.low-content')
                @include('dashboard.partials.menus.order-online')
                @include('dashboard.partials.menus.unit-ditanya')
                @include('dashboard.partials.menus.claim-garansi')
                @include('dashboard.partials.menus.input-claim')
                @include('dashboard.partials.menus.garansi-cermati')
                @include('dashboard.partials.menus.garansi-resmi')
                @include('dashboard.partials.menus.keep-barang')
                @include('dashboard.partials.menus.settings')
                @include('dashboard.partials.menus.nama-stock')
                @include('dashboard.partials.menus.profile')
                @include('dashboard.partials.menus.auth-users')
                @include('dashboard.partials.menus.activity-logs')
                @include('dashboard.partials.menus.program-promo')
                @include('dashboard.partials.menus.bonus-report')
                @include('dashboard.partials.menus.talent-bonus')
                @include('dashboard.partials.menus.editor-performance')
                @include('dashboard.partials.menus.sell-out')
                @include('dashboard.partials.menus.harga-kompetitor')
                @include('dashboard.partials.menus.pricelist-katalog')
                @include('dashboard.partials.menus.apple-katalog')
                @include('dashboard.partials.menus.img-repo')
                @include('dashboard.partials.menus.asset-vendor-inventory')
                @include('dashboard.partials.menus.laporan-event')
                @include('dashboard.partials.menus.ads-log')
                @include('dashboard.partials.menus.budgeting')
                @include('dashboard.partials.menus.market-pasar')
                @include('dashboard.partials.menus.market-intelijen-harga')
                @include('dashboard.partials.menus.market-audit-harga')
                @include('dashboard.partials.menus.market-eksternal')
@verbatim
            </main>
        </div>
    </div>
</div>
@endverbatim
