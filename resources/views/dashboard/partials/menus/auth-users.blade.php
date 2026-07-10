@verbatim
<div v-if="activeTab === 'auth_users'" class="space-y-4 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 md:w-14 md:h-14 rounded-[20px] bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shadow-sm shadow-sky-100/70">
                                        <i class="fa-solid fa-users-gear text-[13px] md:text-[15px]"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h2 class="type-body font-bold text-slate-900">Manajemen User</h2>
                                        <p class="type-body max-w-2xl text-slate-500">Tambah akun dashboard langsung dari panel admin dan pantau daftar user aktif tanpa terminal.</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="summary-counter-pill">{{ authUsers.length }} user</span>
                                    <button @click="loadAuthUsers"
                                        class="secondary-cta-button secondary-cta-neutral active:scale-95">
                                        <i class="fa-solid fa-rotate-right text-[10px]"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="section-card section-card-shell p-4 md:p-6 xl:p-7">
                            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 xl:gap-6">
                                <form class="xl:col-span-4 space-y-4 section-card admin-table-shell bg-gradient-to-b from-sky-50/80 via-white to-white p-4 md:p-5" @submit.prevent="submitAuthUserForm">
                                    <div class="space-y-1">
                                        <div class="type-meta font-bold uppercase tracking-[0.18em] text-sky-500">Tambah User</div>
                                        <p class="type-body text-slate-500">Buat akun baru untuk login dashboard. Username dan PIN akan langsung aktif.</p>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label for="auth-user-username" class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Username</label>
                                            <input id="auth-user-username" name="auth_user_username" v-model="authUserForm.username" type="text" placeholder="mis. kasir"
                                                autocomplete="username"
                                                class="form-input font-bold" />
                                        </div>
                                        <div>
                                            <label for="auth-user-nama" class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Nama</label>
                                            <input id="auth-user-nama" name="auth_user_nama" v-model="authUserForm.nama" type="text" placeholder="Nama user"
                                                autocomplete="name"
                                                class="form-input font-bold" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="auth-user-email" class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Email</label>
                                        <input id="auth-user-email" name="auth_user_email" v-model="authUserForm.email" type="email" placeholder="opsional"
                                            autocomplete="email"
                                            class="form-input" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label for="auth-user-pin" class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">PIN</label>
                                            <input id="auth-user-pin" name="auth_user_pin" v-model="authUserForm.pin" type="password" placeholder="PIN login"
                                                autocomplete="new-password"
                                                class="form-input" />
                                        </div>
                                        <div>
                                            <label for="auth-user-confirm-pin" class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Konfirmasi PIN</label>
                                            <input id="auth-user-confirm-pin" name="auth_user_confirm_pin" v-model="authUserForm.confirmPin" type="password" placeholder="Ulangi PIN"
                                                autocomplete="new-password"
                                                class="form-input" />
                                        </div>
                                    </div>
                                    <button type="submit" :disabled="submittingAuthUser"
                                        class="modal-primary-button w-full modal-primary-button--info shadow-lg shadow-blue-100 active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingAuthUser" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-user-plus"></i>
                                        Simpan User
                                    </button>
                                </form>

                                <div class="xl:col-span-8 min-w-0">
                                    <div class="mb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                        <div>
                                            <div class="type-meta font-bold uppercase tracking-[0.18em] text-slate-400">Daftar User</div>
                                            <p class="type-body text-slate-500">Tampilan tabel diperlebar supaya nama dan email lebih mudah dipindai.</p>
                                        </div>
                                        <span class="status-pill status-pill--neutral">
                                            {{ authUsersLoaded ? 'sinkron' : 'memuat' }}
                                        </span>
                                    </div>
                                    <div class="md:hidden space-y-3">
                                        <article v-if="!authUsersLoaded" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                            <div class="mobile-data-card__summary text-slate-400">Memuat user...</div>
                                        </article>
                                        <article v-else-if="!authUsers.length" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                            <div class="mobile-data-card__summary text-slate-400">Belum ada user tambahan.</div>
                                        </article>
                                        <article v-for="(user, idx) in authUsers.filter(Boolean)" :key="`mobile-user-${user?.ID || idx}`" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                            <div class="mobile-data-card__header">
                                                <div>
                                                    <div class="mobile-data-card__title">{{ user?.nama || user?.username || 'User' }}</div>
                                                    <div class="mobile-data-card__meta">@{{ user?.username || '-' }}</div>
                                                </div>
                                                <span class="entity-badge entity-badge--info">Akun</span>
                                            </div>
                                            <div class="mobile-data-card__summary">{{ user?.email || 'Email belum diisi' }}</div>
                                        </article>
                                    </div>
                                    <div class="hidden md:block admin-table-shell">
                                        <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-100">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Username</th>
                                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama</th>
                                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Email</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                <tr v-if="!authUsersLoaded">
                                                    <td colspan="3" class="px-4 py-6 text-center text-[11px] font-medium text-slate-400">Memuat user...</td>
                                                </tr>
                                                <tr v-else-if="!authUsers.length">
                                                    <td colspan="3" class="px-4 py-6 text-center text-[11px] font-medium text-slate-400">Belum ada user tambahan.</td>
                                                </tr>
                                                <tr v-for="(user, idx) in authUsers.filter(Boolean)" :key="user?.ID || `auth-user-${idx}`">
                                                    <td class="px-4 py-3 text-[12px] font-bold text-slate-800 whitespace-nowrap">{{ user?.username || '-' }}</td>
                                                    <td class="px-4 py-3 text-[12px] text-slate-600 min-w-[220px]">{{ user?.nama || user?.username || '-' }}</td>
                                                    <td class="px-4 py-3 text-[12px] text-slate-500 min-w-[240px]">{{ user?.email || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
@endverbatim
