@verbatim
                <div class="px-6 pb-1 pt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-slate-300">Analisa Konten</div>
<!-- hidden: Top Konten, Low Konten, Insight & Tren
                <div @click="switchTab('top_content_platform')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'top_content_platform' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-trophy text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Top Konten</span>
                    <span v-if="activeTab === 'top_content_platform'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('low_content_platform')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'low_content_platform' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-arrow-trend-down text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Low Konten</span>
                    <span v-if="activeTab === 'low_content_platform'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('analisa_insight')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'analisa_insight' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-microscope text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Insight & Tren</span>
                    <span v-if="activeTab === 'analisa_insight'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
-->
                <div @click="switchTab('meta_story')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'meta_story' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-clapperboard text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Story IG</span>
                    <span v-if="activeTab === 'meta_story'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('meta_feed')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'meta_feed' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-photo-film text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Feed Konten</span>
                    <span v-if="activeTab === 'meta_feed'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
                <div @click="switchTab('meta_followers')"
                    :class="['mx-3 my-px rounded-xl px-4 py-2 flex items-center gap-3 cursor-pointer', activeTab === 'meta_followers' ? 'sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white' : 'nav-idle !text-slate-400 hover:bg-slate-50 hover:!text-slate-700']">
                    <i class="fa-solid fa-user-group text-[11px] lg:text-[12px] w-4"></i>
                    <span class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Followers IG</span>
                    <span v-if="activeTab === 'meta_followers'" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                </div>
@endverbatim
