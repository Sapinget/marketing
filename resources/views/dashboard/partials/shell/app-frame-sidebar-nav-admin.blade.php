@verbatim
                <!-- Claim Garansi (Teknisi only - standalone, no accordion) -->
                <div v-if="isTeknisi" @click="switchTab('claim_garansi_asuransi')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'claim_garansi_asuransi' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                    <div v-if="activeTab === 'claim_garansi_asuransi'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-shield-heart text-body w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium relative z-10">Claim Garansi</span>
                </div>

                <!-- Performa Accordion -->
                <div v-if="!isTeknisi" class="select-none">
                    <div @click="toggleMenuGroup('performa')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['bonus_report','talent_bonus','editor_performance'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-chart-pie text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium">Performa</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', performaOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="performaOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('bonus_report')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'bonus_report' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'bonus_report'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-coins text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Bonus Report</span>
                            </div>
                            <div @click="switchTab('talent_bonus')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'talent_bonus' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'talent_bonus'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-user-tag text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Talent Bonus</span>
                            </div>
                            <div @click="switchTab('editor_performance')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'editor_performance' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'editor_performance'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-clapperboard text-[11px] w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Editor
                                    Performance</span>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Harga & Kompetitor -->
                <div v-if="!isTeknisi" @click="switchTab('harga_kompetitor')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'harga_kompetitor' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                    <div v-if="activeTab === 'harga_kompetitor'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-tags text-body w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium relative z-10">Harga & Kompetitor</span>
                </div>


                <!-- Laporan Event -->
                <div v-if="!isTeknisi" @click="switchTab('laporan_event')"
                    :class="['flex items-center gap-3 px-5 py-3 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'laporan_event' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                    <div v-if="activeTab === 'laporan_event'"
                        class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                    </div>
                    <i
                        class="fa-solid fa-calendar-check text-body w-4 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="type-body font-medium relative z-10">Laporan Event</span>
                </div>

                <!-- Settings Group -->
                <div v-if="!isTeknisi">
                    <div @click="toggleMenuGroup('settings')"
                        :class="['flex items-center justify-between px-5 py-3 cursor-pointer group transition-all duration-300', ['settings','nama_stock','auth_users','activity_logs'].includes(activeTab) ? 'nav-accordion-active' : 'nav-idle']">
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-solid fa-sliders text-body w-4 text-center transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="type-body font-medium">Settings</span>
                        </div>
                        <i
                            :class="['fa-solid fa-chevron-down text-body-sm transition-transform duration-300', settingsGroupOpen ? 'rotate-180' : '']"></i>
                    </div>
                    <transition name="sidebar-accordion">
                        <div v-show="settingsGroupOpen" class="sidebar-accordion-panel">
                            <div @click="switchTab('settings')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'settings' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'settings'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-sliders text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Settings</span>
                            </div>
                            <div @click="switchTab('nama_stock')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'nama_stock' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'nama_stock'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-tag text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Nama Stock</span>
                            </div>
                            <div v-if="canManageUsers" @click="switchTab('auth_users')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'auth_users' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'auth_users'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-users-gear text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Manajemen User</span>
                            </div>
                            <div @click="switchTab('activity_logs')"
                                :class="['flex items-center gap-3 pl-10 pr-5 py-2.5 cursor-pointer relative overflow-hidden group transition-all duration-300', activeTab === 'activity_logs' ? 'sidebar-nav-item-active bg-ppp-accent text-white' : 'nav-idle']">
                                <div v-if="activeTab === 'activity_logs'"
                                    class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300 ease-out">
                                </div>
                                <i
                                    class="fa-solid fa-clock-rotate-left text-body w-3.5 text-center relative z-10 transition-transform duration-300 group-hover:scale-110"></i>
                                <span class="type-body-sm font-medium relative z-10">Activity Logs</span>
                            </div>
                        </div>
                    </transition>
                </div>
@endverbatim
