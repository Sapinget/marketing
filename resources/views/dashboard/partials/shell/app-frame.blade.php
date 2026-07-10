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
        <div v-if="notification.open"
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

        <aside
            :class="['fixed inset-y-0 left-0 z-[80] w-60 bg-ppp-sidebar border-r border-slate-200 flex flex-col transform-gpu will-change-transform transition-[transform,box-shadow] duration-400', isSidebarOpen ? 'translate-x-0 shadow-2xl md:shadow-none' : '-translate-x-[calc(100%+20px)] shadow-none']"
            style="transition-timing-function:cubic-bezier(0.22, 1, 0.36, 1);">
            <div class="px-4 h-16 flex items-center gap-3 border-b border-slate-200">
                <div
                    class="w-9 h-9 bg-white rounded-xl flex items-center justify-center p-1.5 border border-blue-100">
                    <img src="/asset/images/logo.png"
                        class="w-full h-full object-contain" alt="Logo" />
                </div>
                <div>
                    <div class="text-[10px] font-medium text-ppp-nav-text tracking-widest uppercase">Pura Pura
                        Ponsel</div>
                    <div class="text-[9px] text-slate-400 uppercase">Marketing Dashboard</div>
                </div>
            </div>

            <div class="py-2 space-y-1 flex-1 overflow-y-auto">
                <!-- Dashboard Menu -->
                <div v-if="!isTeknisi" @click="switchTab('dashboard')"
                    :class="['flex items-center justify-between px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'dashboard' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-500 hover:bg-slate-50']">
                    <div v-if="activeTab === 'dashboard'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <div class="flex items-center gap-3 relative z-10">
                        <i
                            class="fa-solid fa-gauge text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                        <span class="type-body font-medium tracking-wide">Dashboard</span>
                    </div>
                </div>

                <!-- Konten Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <!-- Accordion Header -->
                    <div @click="toggleMenuGroup('konten')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['master','ideation','distribution','analytics','calendar','story','unboxing'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-folder-open text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Konten</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', kontenOpen ? 'rotate-180' : '']"></i>
                    </div>

                    <!-- Accordion Items -->
                    <transition name="sidebar-accordion">
                        <div v-show="kontenOpen" class="sidebar-accordion-panel">
                            <!-- Master Plan -->
                            <div @click="switchTab('master')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'master' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'master'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-layer-group text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Master Plan</span>
                            </div>

                            <!-- Unboxing -->
                            <div @click="switchTab('unboxing')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'unboxing' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'unboxing'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-box-open text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Unboxing</span>
                            </div>

                            <!-- Ideation -->
                            <div @click="switchTab('ideation')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'ideation' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'ideation'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-lightbulb text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Ideation</span>
                            </div>

                            <!-- Distribution -->
                            <div @click="switchTab('distribution')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'distribution' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'distribution'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-share-nodes text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Distribution</span>
                            </div>

                            <!-- Analytics -->
                            <div @click="switchTab('analytics')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'analytics' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'analytics'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-chart-line text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Analytics</span>
                            </div>

                            <!-- Kalender -->
                            <div @click="switchTab('calendar')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'calendar' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'calendar'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-calendar-alt text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Kalender</span>
                            </div>

                            <!-- Jadwal Story -->
                            <div @click="switchTab('story')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'story' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'story'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Jadwal Story</span>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Marketing Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('marketing')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['program_promo','sell_out','ads_log','budgeting'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-bullseye text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Marketing</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', marketingOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="marketingOpen" class="sidebar-accordion-panel">
                            <!-- Program Promo -->
                            <div @click="switchTab('program_promo')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'program_promo' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'program_promo'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-bullhorn text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Program Promo</span>
                            </div>
                            <!-- Sell Out -->
                            <div @click="switchTab('sell_out')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'sell_out' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'sell_out'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-arrow-trend-up text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Sell Out
                                    Target</span>
                            </div>
                            <!-- Ads Log -->
                            <div @click="switchTab('ads_log')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'ads_log' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'ads_log'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-rectangle-ad text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Ads Log</span>
                            </div>
                            <!-- Budgeting -->
                            <div @click="switchTab('budgeting')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'budgeting' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'budgeting'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-wallet text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Budgeting</span>
                            </div>
                        </div>
                    </transition>
                </div>


                <!-- Analisa Konten Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('analisa')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['top_content_platform','low_content_platform','analisa_insight','meta_story','meta_feed'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-chart-bar text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Analisa Konten</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', analisaKontenOpen ? 'rotate-180' : '']"></i>
                    </div>

                    <transition name="sidebar-accordion">
                        <div v-show="analisaKontenOpen" class="sidebar-accordion-panel">
                            <!-- Top Konten -->
                            <div @click="switchTab('top_content_platform')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'top_content_platform' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'top_content_platform'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-trophy text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Top Konten</span>
                            </div>

                            <!-- Low Konten -->
                            <div @click="switchTab('low_content_platform')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'low_content_platform' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'low_content_platform'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-arrow-trend-down text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Low Konten</span>
                            </div>

                            <!-- Insight & Tren -->
                            <div @click="switchTab('analisa_insight')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'analisa_insight' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'analisa_insight'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-microscope text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Insight &
                                    Tren</span>
                            </div>

                            <!-- Meta: Story IG -->
                            <div @click="switchTab('meta_story')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'meta_story' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <i
                                    class="fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Story IG</span>
                            </div>

                            <!-- Meta: Feed Konten -->
                            <div @click="switchTab('meta_feed')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'meta_feed' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <i
                                    class="fa-solid fa-photo-film text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Feed Konten</span>
                            </div>
                        </div>
                    </transition>
                </div>
                <!-- Customer Service Accordion (non-Teknisi) -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('cs')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['orderan_online','unit_ditanya','claim_garansi_asuransi','keep_barang'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-headset text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Customer Service</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', csOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="csOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('orderan_online')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'orderan_online' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'orderan_online'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-cart-shopping text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Order Online</span>
                            </div>
                            <div @click="switchTab('unit_ditanya')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'unit_ditanya' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'unit_ditanya'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-question-circle text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Unit Ditanya</span>
                            </div>
                            <div @click="switchTab('claim_garansi_asuransi')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'claim_garansi_asuransi'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-shield-heart text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Claim Garansi</span>
                            </div>
                            <div @click="switchTab('keep_barang')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'keep_barang' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'keep_barang'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-box-archive text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Keep Barang</span>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Claim Garansi (Teknisi only - standalone, no accordion) -->
                <div v-if="isTeknisi" @click="switchTab('claim_garansi_asuransi')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-500 hover:bg-slate-50']">
                    <div v-if="activeTab === 'claim_garansi_asuransi'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-shield-heart text-[12px] w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium tracking-wide relative z-10">Claim Garansi</span>
                </div>
                <!-- Performa Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('performa')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['bonus_report','talent_bonus','editor_performance'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-chart-pie text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Performa</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', performaOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="performaOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('bonus_report')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'bonus_report' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'bonus_report'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-coins text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Bonus Report</span>
                            </div>
                            <div @click="switchTab('talent_bonus')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'talent_bonus' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'talent_bonus'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-user-tag text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Talent Bonus</span>
                            </div>
                            <div @click="switchTab('editor_performance')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'editor_performance' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'editor_performance'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Editor
                                    Performance</span>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Harga & Kompetitor -->
                <div v-if="!isTeknisi" @click="switchTab('harga_kompetitor')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'harga_kompetitor' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-500 hover:bg-slate-50']">
                    <div v-if="activeTab === 'harga_kompetitor'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-tags text-[12px] w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium tracking-wide relative z-10">Harga & Kompetitor</span>
                </div>

                <!-- Laporan Event -->
                <div v-if="!isTeknisi" @click="switchTab('laporan_event')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'laporan_event' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-500 hover:bg-slate-50']">
                    <div v-if="activeTab === 'laporan_event'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-calendar-check text-[12px] w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium tracking-wide relative z-10">Laporan Event</span>
                </div>

                <!-- Settings Group -->
                <div v-if="!isTeknisi">
                    <div @click="toggleMenuGroup('settings')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['settings','nama_stock'].includes(activeTab) ? 'text-ppp-accent' : 'text-slate-500 hover:bg-slate-50']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-sliders text-[12px] w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium tracking-wide">Settings</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-[10px] transition-transform duration-300', settingsGroupOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="settingsGroupOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('settings')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'settings' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'settings'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-sliders text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Settings</span>
                            </div>
                            <div @click="switchTab('nama_stock')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'nama_stock' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'nama_stock'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-tag text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Nama Stock</span>
                            </div>
                            <div @click="switchTab('auth_users')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'auth_users' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'auth_users'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-users-gear text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Manajemen User</span>
                            </div>
                            <div @click="switchTab('activity_logs')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'activity_logs' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'activity_logs'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-clock-rotate-left text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Activity Logs</span>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <div class="mt-auto p-4 border-t border-slate-200">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center text-[11px] font-semibold uppercase">
                        {{ (currentUser?.username || 'U')[0] }}</div>
                    <div class="min-w-0">
                        <div class="type-body font-medium text-slate-800 truncate">{{ currentUser.username }}
                        </div>
                        <div class="type-micro text-slate-400 uppercase tracking-widest">{{ currentUser.role }}
                        </div>
                    </div>
                </div>
                <button @click="logout" class="secondary-cta-button secondary-cta-danger w-full">Logout</button>
            </div>
        </aside>

        <div
            :class="['min-h-[100dvh] transform-gpu transition-[padding] duration-500 ease-in-out', isSidebarOpen ? 'md:pl-60' : 'md:pl-0']">
            <header
                class="h-12 md:h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 md:px-6 sticky top-0 z-50">
                <button @click="toggleSidebar" type="button"
                    class="w-10 h-10 text-slate-400 flex items-center justify-center hover:text-ppp-accent transition-colors"
                    aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative" id="profile-menu-wrapper">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                        class="flex items-center gap-2.5 px-2 py-1.5 transition-colors group" id="profile-menu-btn">
                        <div
                            class="w-8 h-8 rounded-full bg-ppp-accent text-white flex items-center justify-center text-[11px] font-semibold uppercase flex-shrink-0">
                            {{ (currentUser.nama || currentUser.username)[0] }}</div>
                        <div class="hidden sm:block text-left">
                            <div
                                class="text-[11px] font-semibold text-slate-800 group-hover:text-ppp-accent leading-tight transition-colors">
                                {{ currentUser.nama || currentUser.username }}</div>
                            <div class="text-[9px] text-slate-400 uppercase tracking-widest leading-tight mt-0.5">{{ currentUser.role }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[8px] text-slate-400 hidden sm:block transition-transform duration-200"
                            :class="profileMenuOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <transition enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-1"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-1">
                        <div v-if="profileMenuOpen"
                            class="absolute right-0 top-[calc(100%+18px)] w-52 bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/60 z-[200] overflow-hidden"
                            id="profile-dropdown">
                            <!-- Menu Items -->
                            <div class="py-1.5">
                                <button @click="openProfileSetting"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-slate-50 transition-colors group"
                                    id="btn-profile-setting">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-blue-50 text-ppp-accent flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-user text-[10px]"></i>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-medium text-slate-700">Profile Setting</div>
                                        <div class="text-[9px] text-slate-400">Ubah data & PIN akses</div>
                                    </div>
                                </button>
                            </div>

                            <!-- Divider + Logout -->
                            <div class="border-t border-slate-100 py-1.5">
                                <button @click="logout"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-red-50 transition-colors group"
                                    id="btn-dropdown-logout">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-red-50 text-red-500 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
                                    </div>
                                    <div class="text-[11px] font-medium text-red-500">Logout</div>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </header>

            <main class="px-3 py-3 md:px-6 md:py-5 space-y-4">
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <template v-for="(item, idx) in breadcrumbItems" :key="idx">
                        <span :class="idx === breadcrumbItems.length - 1 ? 'text-ppp-nav-text font-medium' : ''">{{ item }}</span>
                        <i v-if="idx < breadcrumbItems.length - 1"
                            class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
                    </template>
                </div>
@endverbatim
