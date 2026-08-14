@verbatim
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('complainTracker')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['input_claim','garansi_cermati','garansi_resmi'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-clipboard-list text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium">Complain Traker</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', complainTrackerOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="complainTrackerOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('input_claim')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'input_claim' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'input_claim'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-file-pen text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Input Claim</span>
                            </div>
                            <div @click="switchTab('garansi_cermati')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'garansi_cermati' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'garansi_cermati'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-shield-halved text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Garansi Cermati</span>
                            </div>
                            <div @click="switchTab('garansi_resmi')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'garansi_resmi' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'garansi_resmi'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-screwdriver-wrench text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Garansi Resmi</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
