@verbatim
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Dashboard</div>
                <div @click="switchTab('dashboard')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'dashboard' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-gauge text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Dashboard</span>
                    <span v-if="activeTab === 'dashboard'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>

                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Konten</div>
                <a href="/konten/master-plan" @click="navigateTab($event, 'master')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'master' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-layer-group text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Master Plan</span>
                    <span v-if="activeTab === 'master'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/unboxing" @click="navigateTab($event, 'unboxing')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'unboxing' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-box-open text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Unboxing</span>
                    <span v-if="activeTab === 'unboxing'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/ideation" @click="navigateTab($event, 'ideation')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'ideation' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-lightbulb text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Ideation</span>
                    <span v-if="activeTab === 'ideation'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/distribution" @click="navigateTab($event, 'distribution')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'distribution' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-share-nodes text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Distribution</span>
                    <span v-if="activeTab === 'distribution'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/analytics" @click="navigateTab($event, 'analytics')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'analytics' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-chart-line text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Analytics</span>
                    <span v-if="activeTab === 'analytics'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/calendar" @click="navigateTab($event, 'calendar')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'calendar' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-calendar-days text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Kalender</span>
                    <span v-if="activeTab === 'calendar'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
                <a href="/konten/story" @click="navigateTab($event, 'story')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'story' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clapperboard text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Jadwal Story</span>
                    <span v-if="activeTab === 'story'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </a>
@endverbatim
