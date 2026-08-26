@verbatim
<!-- Profile Setting View -->
                    <div v-if="activeTab === 'profile'" class="space-y-4 animate-fadeIn">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-amber text-light flex items-center justify-center border border-amber">
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
                                class="md:col-span-2 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-8 flex flex-col items-center text-center relative overflow-hidden group">
                                <div
                                    class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-amber-500 to-slate-600 opacity-[0.08] group-hover:opacity-20 transition-opacity duration-500">
                                </div>
                                <label for="avatar-upload-input"
                                    class="relative w-28 h-28 rounded-full bg-amber text-light flex items-center justify-center text-5xl font-bold mb-5 mt-2 border-[6px] border-white overflow-hidden cursor-pointer group/avatar hover:ring-4 hover:ring-amber/30 transition-all duration-200"
                                    :class="submittingAvatar ? 'pointer-events-none' : ''">
                                    <img v-if="currentUser?.avatar_url" :src="currentUser.avatar_url"
                                        class="w-full h-full object-cover" alt="Foto Profil" />
                                    <span v-else>{{ currentUser?.nama?.charAt(0)?.toUpperCase() || 'U' }}</span>
                                    <div v-if="submittingAvatar"
                                        class="absolute inset-0 bg-slate-950/55 flex items-center justify-center">
                                        <i class="fa-solid fa-spinner fa-spin text-white text-2xl"></i>
                                    </div>
                                    <div v-else
                                        class="absolute inset-0 bg-black/0 hover:bg-black/40 flex items-center justify-center transition-all duration-200 group-hover/avatar:bg-black/40">
                                        <i class="fa-solid fa-camera text-white text-xl opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-200"></i>
                                    </div>
                                </label>
                                <input id="avatar-upload-input" name="avatar_upload_input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" :disabled="submittingAvatar" @change="uploadProfileAvatar" />
                                <label for="avatar-upload-input"
                                    class="mt-2 text-body-sm text-slate-400 font-bold uppercase cursor-pointer hover:text-ppp-accent transition-colors"
                                    :class="submittingAvatar ? 'opacity-50 pointer-events-none' : ''">
                                    <i class="fa-solid fa-upload mr-1" :class="submittingAvatar ? 'fa-spin' : ''"></i>
                                    <span v-if="submittingAvatar">Mengunggah...</span>
                                    <span v-else>Ganti Foto</span>
                                </label>
                                <h3 class="text-xl font-bold text-slate-900 mt-4">{{ currentUser?.nama || 'Guest User' }}
                                </h3>
                                <p
                                    class="text-body font-bold text-light uppercase mt-2 px-4 py-1.5 bg-amber rounded-full">
                                    {{ currentUser?.role || 'Marketing' }}</p>

                                <div class="mt-8 w-full space-y-3">
                                    <div
                                        class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <span
                                            class="text-body-sm text-slate-400 font-bold uppercase">Username</span>
                                        <span class="text-body font-bold text-slate-900">{{ currentUser?.username || '-' }}</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <span
                                            class="text-body-sm text-slate-400 font-bold uppercase">Akses</span>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                                            <span
                                                class="text-body-sm font-bold text-success uppercase">Aktif</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Pribadi -->
                            <form @submit.prevent="saveProfileInfo"
                                class="md:col-span-1 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-6 md:p-8 flex flex-col group">
                                <h3 class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-amber text-light flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-id-card text-body"></i>
                                    </div>
                                    Informasi Pribadi
                                </h3>
                                <div class="space-y-4 flex-1">
                                    <!-- class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama -->
                                    <!-- class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal -->
                                    <!-- class="type-meta font-bold text-slate-400 uppercase mb-2">Vendor -->
                                    <div>
                                        <label for="profile-nama-lengkap"
                                            class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Nama
                                            Lengkap</label>
                                        <input id="profile-nama-lengkap" name="profile_nama_lengkap" type="text" v-model="profileForm.namaLengkap" placeholder="Masukkan nama"
                                            autocomplete="name"
                                            class="form-input font-bold" />
                                    </div>
                                    <div>
                                        <label for="profile-role"
                                            class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Posisi
                                            / Role</label>
                                        <input id="profile-role" name="profile_role" type="text" :value="currentUser?.role || 'Marketing'" disabled
                                            class="form-input-disabled" />
                                    </div>
                                </div>
                                <div class="pt-5 mt-auto">
                                    <button type="submit" :disabled="submittingInfo"
                                        class="primary-cta-button w-full primary-cta-button--info active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingInfo" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-floppy-disk"></i>
                                        Simpan Profil
                                    </button>
                                </div>
                            </form>

                            <!-- Ganti Password Keamanan -->
                            <form @submit.prevent="saveProfileSetting"
                                class="md:col-span-1 lg:col-span-4 bg-white radius-dialog border border-slate-100 p-6 md:p-8 flex flex-col group">
                                <h3 class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-danger text-light flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-shield-halved text-body"></i>
                                    </div>
                                    Keamanan (Password)
                                </h3>
                                <div class="sr-only" aria-hidden="true">
                                    <label for="profile-pin-username">Username</label>
                                    <input id="profile-pin-username" name="username" type="text"
                                        :value="currentUser?.username || ''" autocomplete="username" readonly />
                                </div>
                                <div class="space-y-4 flex-1">
                                    <div>
                                        <label for="profile-old-pin"
                                            class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Password
                                            Saat Ini</label>
                                        <input id="profile-old-pin" name="profile_old_pin" type="password" v-model="profileForm.oldPin"
                                            placeholder="Masukkan password saat ini" autocomplete="current-password"
                                            class="form-input" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="profile-new-pin"
                                                class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Password
                                                Baru</label>
                                            <input id="profile-new-pin" name="profile_new_pin" type="password" v-model="profileForm.newPin" placeholder="Password Baru"
                                                autocomplete="new-password"
                                                class="form-input" />
                                        </div>
                                        <div>
                                            <label for="profile-confirm-pin"
                                                class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Konfirmasi</label>
                                            <input id="profile-confirm-pin" name="profile_confirm_pin" type="password" v-model="profileForm.confirmPin"
                                                placeholder="Ulangi password" autocomplete="new-password" class="form-input" />
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-5 mt-auto">
                                    <button type="submit" :disabled="submittingPin"
                                        class="primary-cta-button w-full primary-cta-button--danger active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingPin" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-lock"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
@endverbatim
