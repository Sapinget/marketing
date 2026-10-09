@verbatim
<!-- hidden: Performa (bonus_report, talent_bonus, editor_performance)
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Performa</div>
                <div @click="switchTab('bonus_report')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'bonus_report' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-coins text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Bonus Report</span>
                    <span v-if="activeTab === 'bonus_report'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('talent_bonus')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'talent_bonus' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-user-tag text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Talent Bonus</span>
                    <span v-if="activeTab === 'talent_bonus'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('editor_performance')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'editor_performance' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clapperboard text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Editor Performance</span>
                    <span v-if="activeTab === 'editor_performance'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
-->

                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Tools</div>
                <div @click="switchTab('harga_kompetitor')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'harga_kompetitor' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-tags text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Harga & Kompetitor</span>
                    <span v-if="activeTab === 'harga_kompetitor'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
<!-- hidden: /katalog/template-background
                <a href="/katalog/template-background"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'template_background' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-photo-film text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Template Background</span>
                    <span v-if="activeTab === 'template_background'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
-->
<!-- hidden: /katalog/android
                <a href="/katalog/android" @click="switchTab('pricelist_katalog')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'pricelist_katalog' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-book-open text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Katalog Pricelist</span>
                    <span v-if="activeTab === 'pricelist_katalog'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
-->
<!-- hidden: /katalog/apple
                <a href="/katalog/apple"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'apple_katalog' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-brands fa-apple text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Katalog Apple</span>
                    <span v-if="activeTab === 'apple_katalog'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
-->
<!-- hidden: /repository-gambar
                <a v-if="canManageSettings" href="/repository-gambar"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'img_repo' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-images text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Repo Gambar</span>
                    <span v-if="activeTab === 'img_repo'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
-->
                <a v-if="canManageSettings" href="/ecommerce/tiktok-template"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'tiktok_template' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-brands fa-tiktok text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Template TikTok</span>
                    <span v-if="activeTab === 'tiktok_template'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <div @click="switchTab('laporan_event')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'laporan_event' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-calendar-check text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Laporan Event</span>
                    <span v-if="activeTab === 'laporan_event'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>

                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Settings</div>
                <div @click="switchTab('settings')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'settings' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-sliders text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Settings</span>
                    <span v-if="activeTab === 'settings'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('nama_stock')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'nama_stock' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-tag text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Nama Stock</span>
                    <span v-if="activeTab === 'nama_stock'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="canManageUsers" @click="switchTab('auth_users')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'auth_users' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-users-gear text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Manajemen User</span>
                    <span v-if="activeTab === 'auth_users'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('activity_logs')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'activity_logs' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clock-rotate-left text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Activity Logs</span>
                    <span v-if="activeTab === 'activity_logs'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
