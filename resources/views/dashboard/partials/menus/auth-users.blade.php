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
                                    <button @click="showAuthUserModal = true"
                                        class="primary-cta-button active:scale-95">
                                        <i class="fa-solid fa-user-plus text-[10px]"></i> Tambah User
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="section-card section-card-shell p-4 md:p-6 xl:p-7">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <div class="type-meta font-bold uppercase tracking-[0.18em] text-slate-400">Daftar User</div>
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
                                <article v-for="(user, idx) in authUsers.filter(Boolean)" :key="`mobile-user-${user?.ID || idx}`"
                                    class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
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
                        </section>

                        <div v-if="showAuthUserModal"
                            class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                            <div @click="showAuthUserModal = false"
                                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm overlay-backdrop"></div>
                            <div class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface">
                                <div class="modal-header-bar radius-sheet-top">
                                    <div class="modal-header-copy">
                                        <div class="modal-header-icon bg-sky-500 text-white">
                                            <i class="fa-solid fa-user-plus text-[14px]"></i>
                                        </div>
                                        <div>
                                            <div class="type-title font-bold text-slate-800">Tambah User</div>
                                            <div class="type-meta text-slate-400 uppercase tracking-widest">Manajemen User</div>
                                        </div>
                                    </div>
                                    <button @click="showAuthUserModal = false"
                                        class="icon-utility-button icon-utility-danger">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                                <form @submit.prevent="submitAuthUserForm" class="flex-1 overflow-y-auto p-6 space-y-4">
                                    <div class="sr-only" aria-hidden="true">
                                        <label for="modal-auth-user-username-hint">Username</label>
                                        <input id="modal-auth-user-username-hint" type="text" name="username"
                                            :value="currentUser?.username || ''" autocomplete="username" readonly />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label for="modal-auth-user-username"
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Username <span class="text-red-500">*</span></label>
                                            <input id="modal-auth-user-username" name="auth_user_username"
                                                v-model="authUserForm.username" type="text" placeholder="mis. kasir"
                                                autocomplete="off" class="form-input font-bold" />
                                        </div>
                                        <div>
                                            <label for="modal-auth-user-nama"
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Nama <span class="text-red-500">*</span></label>
                                            <input id="modal-auth-user-nama" name="auth_user_nama"
                                                v-model="authUserForm.nama" type="text" placeholder="Nama lengkap"
                                                autocomplete="off" class="form-input" />
                                        </div>
                                    </div>
                                    <div>
                                        <label for="modal-auth-user-email"
                                            class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Email</label>
                                        <input id="modal-auth-user-email" name="auth_user_email"
                                            v-model="authUserForm.email" type="email" placeholder="opsional"
                                            autocomplete="off" class="form-input" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="modal-auth-user-pin"
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">PIN <span class="text-red-500">*</span></label>
                                            <input id="modal-auth-user-pin" name="auth_user_pin"
                                                v-model="authUserForm.pin" type="password" placeholder="PIN login"
                                                autocomplete="new-password" class="form-input" />
                                        </div>
                                        <div>
                                            <label for="modal-auth-user-confirm-pin"
                                                class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Konfirmasi <span class="text-red-500">*</span></label>
                                            <input id="modal-auth-user-confirm-pin" name="auth_user_confirm_pin"
                                                v-model="authUserForm.confirmPin" type="password" placeholder="Ulangi PIN"
                                                autocomplete="new-password" class="form-input" />
                                        </div>
                                    </div>
                                </form>
                                <div class="modal-footer-bar modal-footer-actions">
                                    <button type="button" @click="showAuthUserModal = false"
                                        class="modal-secondary-button">Batal</button>
                                    <button type="button" @click="submitAuthUserForm" :disabled="submittingAuthUser"
                                        class="modal-primary-button modal-primary-button--info shadow-sm active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingAuthUser" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-user-plus"></i>
                                        Simpan User
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
@endverbatim
