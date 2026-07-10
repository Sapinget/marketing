@verbatim
<!-- Dashboard marketing lengkap berjalan di Laravel -->
<div id="app" class="min-h-[100dvh]" v-cloak>
    <transition name="fade">
        <div v-if="appLoading"
            class="fixed inset-0 z-[9999] bg-white flex flex-col items-center justify-center gap-6">
            <div
                class="loading-logo w-20 h-20 bg-white flex items-center justify-center p-3 border border-blue-50 radius-panel">
                <img src="/asset/images/logo.png"
                    class="w-full h-full object-contain" alt="Logo" />
            </div>
            <div class="text-center">
                <div class="text-[11px] font-medium text-ppp-nav-text tracking-[0.2em] uppercase">Pura Pura Ponsel
                </div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-2">Menyiapkan Dashboard...</div>
            </div>
        </div>
    </transition>

    <div v-if="runtimeError"
        class="fixed top-3 left-1/2 -translate-x-1/2 z-[10000] w-[94%] max-w-3xl bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-medium uppercase tracking-widest">Sistem Error</div>
                <div class="text-[11px] leading-relaxed break-words mt-0.5">{{ runtimeError }}</div>
            </div>
            <button @click="runtimeError = null" class="text-red-400 hover:text-red-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <transition name="toast">
        <div v-if="notification && notification.open"
            :class="['fixed bottom-6 left-1/2 -translate-x-1/2 z-[10000] px-5 py-3 rounded-2xl text-[11px] border shadow-xl flex items-center gap-3 min-w-[220px] max-w-[92vw]', notification.type === 'error' ? 'bg-rose-50 text-rose-700 border-rose-200' : notification.type === 'warning' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200']">
            <div
                :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0', notification.type === 'error' ? 'bg-rose-100 text-rose-500' : notification.type === 'warning' ? 'bg-amber-100 text-amber-500' : 'bg-emerald-100 text-emerald-500']">
                <i :class="['fa-solid text-[12px]', notification.icon]"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-widest">
                    {{ notification.type === 'error' ? 'Error' : notification.type === 'warning' ? 'Perhatian' : 'Berhasil' }}
                </div>
                <div class="mt-0.5 break-words leading-relaxed">{{ notification.message }}</div>
            </div>
        </div>
    </transition>

    <transition name="fade">
        <div v-if="!currentUser && !appLoading"
            class="min-h-[100dvh] bg-white flex items-center justify-center p-6">
            <div class="w-full max-w-[360px] text-center">
                <img src="/asset/images/logo.png" class="w-20 h-20 mx-auto mb-8"
                    alt="Logo" />
                <h1 class="text-2xl font-semibold text-slate-900 mb-2">Selamat Datang</h1>
                <p class="text-[11px] text-slate-400 mb-8 uppercase tracking-[0.2em]">Login untuk membuka dashboard
                </p>

                <form class="space-y-4 mb-8" @submit.prevent="handleLogin">
                    <div class="relative">
                        <label for="login-username" class="sr-only">Username</label>
                        <i
                            class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input id="login-username" name="username" v-model="loginForm.username" type="text" placeholder="Username"
                            autocomplete="username"
                            class="form-input-auth" />
                    </div>
                    <div class="relative">
                        <label for="login-pin" class="sr-only">PIN Akses</label>
                        <i
                            class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input id="login-pin" name="pin" v-model="loginForm.pin" type="password" placeholder="PIN Akses"
                            autocomplete="current-password"
                            class="form-input-auth" @keyup.enter="handleLogin" />
                    </div>
                </form>

                <button @click="handleLogin" :disabled="submitting"
                    class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[11px] font-medium uppercase tracking-[0.2em] hover:bg-black transition-all disabled:opacity-50">{{ submitting ? 'Mengecek...' : 'Masuk Ke Sistem' }}</button>
            </div>
        </div>
    </transition>

    <div v-if="currentUser && !appLoading" class="min-h-[100dvh] bg-slate-50">
        <transition name="fade">
            <div v-if="isSidebarOpen" data-sidebar-backdrop @click="closeSidebar" class="fixed inset-0 z-[70] bg-slate-900/30 md:hidden">
            </div>
        </transition>
@endverbatim
        @include('dashboard.partials.shell.app-frame-sidebar')
@verbatim
        <div
            :class="['min-h-[100dvh] transform-gpu transition-[padding] duration-500 ease-in-out', isSidebarOpen ? 'md:pl-60' : 'md:pl-0']">
@endverbatim
            @include('dashboard.partials.shell.app-frame-header')
@verbatim

            <main class="px-3 py-3 md:px-6 md:py-5 space-y-4">
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <template v-for="(item, idx) in breadcrumbItems" :key="idx">
                        <span :class="idx === breadcrumbItems.length - 1 ? 'text-ppp-nav-text font-medium' : ''">{{ item }}</span>
                        <i v-if="idx < breadcrumbItems.length - 1"
                            class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
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
                @include('dashboard.partials.menus.unboxing')
                @include('dashboard.partials.menus.top-content')
                @include('dashboard.partials.menus.low-content')
                @include('dashboard.partials.menus.order-online')
                @include('dashboard.partials.menus.unit-ditanya')
                @include('dashboard.partials.menus.claim-garansi')
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
                @include('dashboard.partials.menus.asset-vendor-inventory')
                @include('dashboard.partials.menus.laporan-event')
                @include('dashboard.partials.menus.ads-log')
                @include('dashboard.partials.menus.budgeting')
@verbatim
            </main>
        </div>
    </div>
</div>
@endverbatim
