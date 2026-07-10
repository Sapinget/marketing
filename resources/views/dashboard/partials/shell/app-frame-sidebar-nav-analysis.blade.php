@verbatim
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
                            <div @click="switchTab('top_content_platform')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'top_content_platform' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'top_content_platform'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-trophy text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Top Konten</span>
                            </div>
                            <div @click="switchTab('low_content_platform')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'low_content_platform' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'low_content_platform'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-arrow-trend-down text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Low Konten</span>
                            </div>
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
                            <div @click="switchTab('meta_story')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'meta_story' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <i
                                    class="fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Story IG</span>
                            </div>
                            <div @click="switchTab('meta_feed')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'meta_feed' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <i
                                    class="fa-solid fa-photo-film text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Feed Konten</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
