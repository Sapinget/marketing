@verbatim
                <div v-if="isTeknisi" class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Teknisi</div>
                <div v-if="isTeknisi" @click="switchTab('claim_garansi_asuransi')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-shield-heart text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Claim Garansi</span>
                    <span v-if="activeTab === 'claim_garansi_asuransi'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>

                <div v-if="!isTeknisi" class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Performa</div>
                <div v-if="!isTeknisi" @click="switchTab('bonus_report')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'bonus_report' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-coins text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Bonus Report</span>
                    <span v-if="activeTab === 'bonus_report'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('talent_bonus')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'talent_bonus' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-user-tag text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Talent Bonus</span>
                    <span v-if="activeTab === 'talent_bonus'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('editor_performance')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'editor_performance' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clapperboard text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Editor Performance</span>
                    <span v-if="activeTab === 'editor_performance'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>

                <div v-if="!isTeknisi" class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Tools</div>
                <div v-if="!isTeknisi" @click="switchTab('harga_kompetitor')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'harga_kompetitor' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-tags text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Harga & Kompetitor</span>
                    <span v-if="activeTab === 'harga_kompetitor'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('pricelist_katalog')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'pricelist_katalog' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-book-open text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Katalog Android</span>
                    <span v-if="activeTab === 'pricelist_katalog'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('apple_katalog')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'apple_katalog' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-brands fa-apple text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Katalog Apple</span>
                    <span v-if="activeTab === 'apple_katalog'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="canManageSettings" @click="switchTab('img_repo')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'img_repo' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-images text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Repo Gambar</span>
                    <span v-if="activeTab === 'img_repo'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('laporan_event')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'laporan_event' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-calendar-check text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Laporan Event</span>
                    <span v-if="activeTab === 'laporan_event'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>

                <div v-if="!isTeknisi" class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400">Settings</div>
                <div v-if="!isTeknisi" @click="switchTab('settings')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'settings' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-sliders text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Settings</span>
                    <span v-if="activeTab === 'settings'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div v-if="!isTeknisi" @click="switchTab('nama_stock')"
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
                <div v-if="!isTeknisi" @click="switchTab('activity_logs')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'activity_logs' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clock-rotate-left text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Activity Logs</span>
                    <span v-if="activeTab === 'activity_logs'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
