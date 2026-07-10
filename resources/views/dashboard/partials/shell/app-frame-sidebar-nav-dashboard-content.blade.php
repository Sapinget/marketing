@verbatim
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

                    <transition name="sidebar-accordion">
                        <div v-show="kontenOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('master')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'master' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'master'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-layer-group text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Master Plan</span>
                            </div>
                            <div @click="switchTab('unboxing')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'unboxing' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'unboxing'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-box-open text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Unboxing</span>
                            </div>
                            <div @click="switchTab('ideation')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'ideation' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'ideation'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-lightbulb text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Ideation</span>
                            </div>
                            <div @click="switchTab('distribution')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'distribution' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'distribution'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-share-nodes text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Distribution</span>
                            </div>
                            <div @click="switchTab('analytics')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'analytics' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'analytics'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-chart-line text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Analytics</span>
                            </div>
                            <div @click="switchTab('calendar')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'calendar' ? 'sidebar-nav-item-active bg-gradient-to-r from-ppp-accent to-[#3D4FDB] text-white' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600']">
                                <div v-if="activeTab === 'calendar'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-calendar-alt text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-meta font-medium tracking-wide relative z-10">Kalender</span>
                            </div>
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
@endverbatim
