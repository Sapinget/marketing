@verbatim
<!-- Profile Setting View -->
                    <div v-if="activeTab === 'profile'" class="space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                        <i class="fa-solid fa-user-gear text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Profile Setting</h2>
                                        <p class="type-body text-slate-500">Kelola informasi profil dan pengaturan
                                            akun</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-5 auto-rows-min">
                            <!-- User Avatar & Identity -->
                            <div
                                class="md:col-span-2 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-8 flex flex-col items-center text-center shadow-sm relative overflow-hidden group">
                                <div
                                    class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-indigo-500 to-purple-600 opacity-[0.08] group-hover:opacity-20 transition-opacity duration-500">
                                </div>
                                <div
                                    class="relative w-28 h-28 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-5xl font-bold mb-5 mt-2 border-[6px] border-white shadow-xl">
                                    {{ currentUser?.nama?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">{{ currentUser?.nama || 'Guest User' }}
                                </h3>
                                <p
                                    class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest mt-2 px-4 py-1.5 bg-indigo-50 rounded-full">
                                    {{ currentUser?.role || 'Marketing' }}</p>

                                <div class="mt-8 w-full space-y-3">
                                    <div
                                        class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <span
                                            class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Username</span>
                                        <span class="text-[12px] font-bold text-slate-900">{{ currentUser?.username || '-' }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <span
                                            class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Akses</span>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                            <span
                                                class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Aktif</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Pribadi -->
                            <div
                                class="md:col-span-1 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-6 md:p-8 shadow-sm flex flex-col group">
                                <h3 class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-id-card text-[12px]"></i>
                                    </div>
                                    Informasi Pribadi
                                </h3>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <label
                                            class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Nama
                                            Lengkap</label>
                                        <input type="text" v-model="profileForm.namaLengkap" placeholder="Masukkan nama"
                                            class="form-input font-bold" />
                                    </div>
                                    <div>
                                        <label
                                            class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Posisi
                                            / Role</label>
                                        <input type="text" :value="currentUser?.role || 'Marketing'" disabled
                                            class="form-input-disabled" />
                                    </div>
                                </div>
                                <div class="pt-5 mt-auto">
                                    <button @click="saveProfileInfo" :disabled="submittingInfo"
                                        class="modal-primary-button w-full modal-primary-button--info shadow-lg shadow-blue-100 active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingInfo" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-floppy-disk"></i>
                                        Simpan Profil
                                    </button>
                                </div>
                            </div>

                            <!-- Ganti PIN Keamanan -->
                            <div
                                class="md:col-span-1 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-6 md:p-8 shadow-sm flex flex-col group">
                                <h3 class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-shield-halved text-[12px]"></i>
                                    </div>
                                    Keamanan (PIN)
                                </h3>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <label
                                            class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">PIN
                                            Saat Ini</label>
                                        <input type="password" v-model="profileForm.oldPin"
                                            placeholder="Masukkan PIN saat ini" class="form-input" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">PIN
                                                Baru</label>
                                            <input type="password" v-model="profileForm.newPin" placeholder="PIN Baru"
                                                class="form-input" />
                                        </div>
                                        <div>
                                            <label
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Konfirmasi</label>
                                            <input type="password" v-model="profileForm.confirmPin"
                                                placeholder="Ulangi PIN" class="form-input" />
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-5 mt-auto">
                                    <button @click="saveProfileSetting" :disabled="submittingPin"
                                        class="modal-primary-button w-full modal-primary-button--danger shadow-lg shadow-rose-100 active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingPin" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-lock"></i>
                                        Update PIN
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
@endverbatim
