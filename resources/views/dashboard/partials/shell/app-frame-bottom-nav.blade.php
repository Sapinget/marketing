@verbatim
        <div class="bottom-nav lg:hidden fixed inset-x-0 bottom-0 z-40">
            <button @click="switchTab('dashboard')"
                :class="['flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl mx-1 my-1 transition-all duration-200', activeTab === 'dashboard' ? 'bg-white/60 text-[var(--ppp-accent)]' : 'text-slate-400']"
                aria-label="Dashboard">
                <i class="fa-solid fa-chart-pie text-[18px]"></i>
                <span class="text-[9px] font-medium uppercase tracking-wide">Dashboard</span>
            </button>

            <button @click="switchTab('master')"
                :class="['flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl mx-1 my-1 transition-all duration-200', activeTab === 'master' ? 'bg-white/60 text-[var(--ppp-accent)]' : 'text-slate-400']"
                aria-label="Master Plan">
                <i class="fa-solid fa-layer-group text-[18px]"></i>
                <span class="text-[9px] font-medium uppercase tracking-wide">Konten</span>
            </button>

            <button @click="switchTab('program_promo')"
                :class="['flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl mx-1 my-1 transition-all duration-200', activeTab === 'program_promo' ? 'bg-white/60 text-[var(--ppp-accent)]' : 'text-slate-400']"
                aria-label="Program Promo">
                <i class="fa-solid fa-bullhorn text-[18px]"></i>
                <span class="text-[9px] font-medium uppercase tracking-wide">Marketing</span>
            </button>

            <button @click="switchTab('orderan_online')"
                :class="['flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl mx-1 my-1 transition-all duration-200', activeTab === 'orderan_online' ? 'bg-white/60 text-[var(--ppp-accent)]' : 'text-slate-400']"
                aria-label="Order Online">
                <i class="fa-solid fa-cart-shopping text-[18px]"></i>
                <span class="text-[9px] font-medium uppercase tracking-wide">CS</span>
            </button>

            <button @click="openBottomNavMore"
                :class="['flex flex-col items-center justify-center gap-0.5 py-2 px-3 rounded-xl mx-1 my-1 transition-all duration-200', bottomNavMoreOpen ? 'bg-white/60 text-[var(--ppp-accent)]' : 'text-slate-400']"
                aria-label="More">
                <i class="fa-solid fa-ellipsis text-[18px]"></i>
                <span class="text-[9px] font-medium uppercase tracking-wide">Lainnya</span>
            </button>
        </div>

        <transition name="fade">
            <div v-if="bottomNavMoreOpen" @click="closeBottomNavMore" class="bottom-nav-sheet-backdrop fixed inset-0 z-[90] lg:hidden"></div>
        </transition>

        <transition name="bottom-nav-sheet">
            <div v-if="bottomNavMoreOpen" class="bottom-nav-sheet fixed inset-x-0 bottom-0 z-[90] bg-white rounded-t-[28px] p-4 max-h-[70vh] overflow-y-auto lg:hidden">
                <div class="mx-auto mb-3 h-1 w-9 rounded-full bg-slate-200"></div>

                <div class="px-2 pb-1.5 pt-1 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Dashboard</div>
                <button @click="switchTab('dashboard'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-gauge text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Dashboard</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Konten</div>
                <button @click="switchTab('master'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-layer-group text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Master Plan</span>
                </button>
                <button @click="switchTab('unboxing'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-box-open text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Unboxing</span>
                </button>
                <button @click="switchTab('ideation'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-lightbulb text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Ideation</span>
                </button>
                <button @click="switchTab('distribution'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-share-nodes text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Distribution</span>
                </button>
                <button @click="switchTab('analytics'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-chart-line text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Analytics</span>
                </button>
                <button @click="switchTab('calendar'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-calendar-days text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Kalender</span>
                </button>
                <button @click="switchTab('story'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-clapperboard text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Jadwal Story</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Market</div>
                <button @click="switchTab('program_promo'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-bullhorn text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Program Promo</span>
                </button>
                <button @click="switchTab('sell_out'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-arrow-trend-up text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Sell Out Target</span>
                </button>
                <button @click="switchTab('ads_log'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-rectangle-ad text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Ads Log</span>
                </button>
                <button @click="switchTab('budgeting'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-wallet text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Budgeting</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Customer Service</div>
                <button @click="switchTab('orderan_online'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-cart-shopping text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Order Online</span>
                </button>
                <button @click="switchTab('unit_ditanya'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-circle-question text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Unit Ditanya</span>
                </button>
                <button @click="switchTab('claim_garansi_asuransi'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-shield-heart text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Claim Garansi</span>
                </button>
                <button @click="switchTab('keep_barang'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-box-archive text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Keep Barang</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Analisa Konten</div>
                <button @click="switchTab('top_content_platform'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-trophy text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Top Konten</span>
                </button>
                <button @click="switchTab('low_content_platform'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-arrow-trend-down text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Low Konten</span>
                </button>
                <button @click="switchTab('analisa_insight'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-microscope text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Insight & Tren</span>
                </button>
                <button @click="switchTab('meta_story'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-clapperboard text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Story IG</span>
                </button>
                <button @click="switchTab('meta_feed'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-photo-film text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Feed Konten</span>
                </button>
                <button @click="switchTab('meta_followers'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-user-group text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Followers IG</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Intelijen Pasar</div>
                <button @click="switchTab('market_pasar'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-cart-shopping text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Pasar</span>
                </button>
                <button @click="switchTab('market_intelijen_harga'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-tag text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Intelijen Harga</span>
                </button>
                <button @click="switchTab('market_audit_harga'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-magnifying-glass-dollar text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Audit Harga</span>
                </button>
                <button @click="switchTab('market_eksternal'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-chart-simple text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Semua Kompetitor</span>
                </button>
                <button @click="switchTab('market_ext_goodponsel'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-circle-dot text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Good Ponsel</span>
                </button>
                <button @click="switchTab('market_ext_devstore'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-circle-dot text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Devstore</span>
                </button>
                <button @click="switchTab('market_ext_rumahgadget'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-circle-dot text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Rumah Gadget Bali</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Complain Traker</div>
                <button @click="switchTab('input_claim'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-file-pen text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Input Claim</span>
                </button>
                <button @click="switchTab('garansi_cermati'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-shield-halved text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Garansi Cermati</span>
                </button>
                <button @click="switchTab('garansi_resmi'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-screwdriver-wrench text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Garansi Resmi</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Performa</div>
                <button @click="switchTab('bonus_report'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-coins text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Bonus Report</span>
                </button>
                <button @click="switchTab('talent_bonus'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-user-tag text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Talent Bonus</span>
                </button>
                <button @click="switchTab('editor_performance'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-clapperboard text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Editor Performance</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Tools</div>
                <button @click="switchTab('harga_kompetitor'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-tags text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Harga & Kompetitor</span>
                </button>
                <button @click="switchTab('pricelist_katalog'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-book-open text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Katalog Android</span>
                </button>
                <button @click="switchTab('apple_katalog'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-brands fa-apple text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Katalog Apple</span>
                </button>
                <button v-if="canManageSettings" @click="switchTab('img_repo'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-images text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Repo Gambar</span>
                </button>
                <button @click="switchTab('laporan_event'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-calendar-check text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Laporan Event</span>
                </button>

                <div class="px-2 pb-1.5 pt-3 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Settings</div>
                <button @click="switchTab('settings'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-sliders text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Settings</span>
                </button>
                <button @click="switchTab('nama_stock'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-tag text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Nama Stock</span>
                </button>
                <button v-if="canManageUsers" @click="switchTab('auth_users'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-users-gear text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Manajemen User</span>
                </button>
                <button @click="switchTab('activity_logs'); closeBottomNavMore()" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-[var(--ppp-accent-soft)] text-[var(--ppp-accent)]"><i class="fa-solid fa-clock-rotate-left text-[15px]"></i></span>
                    <span class="text-[12px] font-medium text-slate-700">Activity Logs</span>
                </button>

                <div style="height: max(12px, env(safe-area-inset-bottom))"></div>
            </div>
        </transition>
@endverbatim
