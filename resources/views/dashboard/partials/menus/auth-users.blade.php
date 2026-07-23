@verbatim
<div v-if="activeTab === 'auth_users'" class="space-y-4 animate-fadeIn pb-10">



                        <section class="section-card section-card-shell">
                            <div class="table-toolbar-shell">
                                <div class="table-toolbar-shell__left">
                                    <div class="relative flex-1">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input id="auth-user-search" name="auth_user_search" :value="authUserSearchQuery"
                                            @input="authUserSearchQuery = $event.target.value" type="text"
                                            placeholder="Cari username / nama / email..." class="form-input-search" />
                                    </div>
                                </div>
                                <div class="table-toolbar-shell__right">
                                    <span class="status-pill status-pill--neutral justify-center">
                                        {{ authUsersLoaded ? 'sinkron' : 'memuat' }}
                                    </span>
                                    <button @click="loadAuthUsers"
                                        class="select-trigger-button select-trigger-button-compact justify-center">
                                        <span class="text-slate-700 font-medium">Refresh</span>
                                        <i class="fa-solid fa-rotate-right text-body-sm text-slate-300"></i>
                                    </button>
                                    <button @click="openAuthUserModal('create')"
                                        class="primary-cta-button primary-cta-button--accent active:scale-95">
                                        <i class="fa-solid fa-user-plus"></i>
                                        <span>Tambah User</span>
                                    </button>
                                </div>
                            </div>

                            <div class="md:hidden space-y-3 p-3">
                                    <article v-if="!authUsersLoaded" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Memuat user...</div>
                                    </article>
                                    <article v-else-if="!filteredAuthUsers.length" class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__summary text-slate-400">Belum ada user tambahan.</div>
                                    </article>
                                    <article v-for="(user, idx) in filteredAuthUsers" :key="`mobile-user-${user?.ID || idx}`"
                                        class="stat-card mobile-record-card mobile-data-card animate-fadeIn">
                                        <div class="mobile-data-card__header">
                                            <div
                                                class="w-10 h-10 rounded-full bg-ppp-accent text-white flex items-center justify-center text-body font-semibold uppercase flex-shrink-0 overflow-hidden">
                                                <img v-if="user?.avatar_url" :src="resolveAvatarUrl(user?.avatar_url)"
                                                    class="w-full h-full object-cover" alt="Foto User"
                                                    @error="markAuthUserAvatarFailed(user)" />
                                                <span v-else>{{ ((user?.nama || user?.username || 'U')[0] || 'U').toUpperCase() }}</span>
                                            </div>
                                            <div>
                                                <div class="mobile-data-card__title">{{ user?.nama || user?.username || 'User' }}</div>
                                                <div class="mobile-data-card__meta">@{{ user?.username || '-' }}</div>
                                            </div>
                                            <span class="entity-badge entity-badge--info">{{ user?.role || 'Akun' }}</span>
                                        </div>
                                        <div class="mobile-data-card__summary">
                                            <div>{{ user?.email || 'Email belum diisi' }}</div>
                                            <div class="type-body-sm text-slate-400 uppercase mt-2">Role: {{ user?.role || '-' }}</div>
                                        </div>
                                        <div class="mobile-data-card__actions">
                                            <div class="type-body-sm text-slate-400 line-clamp-1">@{{ user?.username || '-' }}</div>
                                            <div class="flex items-center gap-2">
                                                <button @click="openAuthUserModal('edit', user)"
                                                    class="table-action-button table-action-compact" aria-label="Edit">
                                                    <i class="fa-solid fa-pen-to-square text-overline"></i>
                                                </button>
                                                <button @click="removeAuthUser(user)"
                                                    class="table-action-button table-action-compact table-action-danger" aria-label="Hapus">
                                                    <i class="fa-solid fa-trash-can text-overline"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                            </div>
                            <div class="hidden md:block overflow-auto">
                                    <table class="w-full text-body-sm">
                                        <thead>
                                            <tr class="table-header-row">
                                                <th class="table-header-cell text-center w-24">Aksi</th>
                                                <th class="table-header-cell text-left">User</th>
                                                <th class="table-header-cell text-left">Nama</th>
                                                <th class="table-header-cell text-left">Role</th>
                                                <th class="table-header-cell text-left">Email</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr v-if="!authUsersLoaded">
                                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-body">Memuat user...</td>
                                            </tr>
                                            <tr v-else-if="!filteredAuthUsers.length">
                                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-body">Belum ada user tambahan.</td>
                                            </tr>
                                            <tr v-for="(user, idx) in filteredAuthUsers" :key="user?.ID || `auth-user-${idx}`"
                                                class="hover:bg-slate-50 transition">
                                                <td class="px-4 py-2.5">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button @click="openAuthUserModal('edit', user)"
                                                            class="table-action-button table-action-compact" aria-label="Edit">
                                                            <i class="fa-solid fa-pen-to-square text-body-sm"></i>
                                                        </button>
                                                        <button @click="removeAuthUser(user)"
                                                            class="table-action-button table-action-compact table-action-danger" aria-label="Hapus">
                                                            <i class="fa-solid fa-trash-can text-body-sm"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 text-slate-700 font-medium whitespace-nowrap">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-9 h-9 rounded-full bg-ppp-accent text-white flex items-center justify-center text-body-sm font-semibold uppercase flex-shrink-0 overflow-hidden">
                                                            <img v-if="user?.avatar_url" :src="resolveAvatarUrl(user?.avatar_url)"
                                                                class="w-full h-full object-cover" alt="Foto User"
                                                                @error="markAuthUserAvatarFailed(user)" />
                                                            <span v-else>{{ ((user?.nama || user?.username || 'U')[0] || 'U').toUpperCase() }}</span>
                                                        </div>
                                                        <span>@{{ user?.username || '-' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 text-slate-600 min-w-[220px]">{{ user?.nama || user?.username || '-' }}</td>
                                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap">
                                                    <span class="entity-badge entity-badge--info">{{ user?.role || '-' }}</span>
                                                </td>
                                                <td class="px-4 py-2.5 text-slate-600 min-w-[240px]">{{ user?.email || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <div class="px-4 py-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-body-sm text-slate-400 font-medium">
                                    <template v-if="filteredAuthUsers.length > 0">{{ filteredAuthUsers.length }} dari {{ authUsers.length }} user</template>
                                    <template v-else>0 user</template>
                                </div>
                                <span class="type-body-sm text-slate-400 uppercase">Daftar user</span>
                            </div>
                        </section>

                    </div>
                    <teleport to="body">
                        <transition name="fade">
                            <div v-if="showAuthUserModal"
                                class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet">
                                <div @click="closeAuthUserModal"
                                    class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm overlay-backdrop"></div>
                                <div class="mobile-sheet modal-width-form relative w-full bg-white radius-sheet border border-slate-200 overflow-hidden animate-fadeIn flex flex-col max-h-[90dvh]">
                                    <div class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]">
                                        <div class="modal-header-copy">
                                            <div class="modal-header-icon bg-secondary text-light border border-slate-200">
                                                <i :class="['fa-solid text-heading-sm', authUserFormMode === 'edit' ? 'fa-pen-to-square' : 'fa-user-plus']"></i>
                                            </div>
                                            <div>
                                                <div class="type-heading-sm text-slate-900">{{ authUserFormMode === 'edit' ? 'Edit User' : 'Tambah User' }}</div>
                                                <div class="type-body-sm text-slate-400 uppercase mt-0.5">Manajemen User</div>
                                            </div>
                                        </div>
                                        <button @click="closeAuthUserModal" class="icon-utility-button icon-utility-round">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                    <form @submit.prevent="submitAuthUserForm" class="flex flex-1 flex-col min-h-0">
                                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                                            <div v-if="authUserFormMode === 'edit'" class="flex flex-col items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <label for="auth-user-avatar-input"
                                                    class="relative w-24 h-24 rounded-full bg-amber text-light flex items-center justify-center text-3xl font-bold overflow-hidden cursor-pointer hover:ring-4 hover:ring-amber/30 transition-all"
                                                    :class="submittingAuthUserAvatar ? 'pointer-events-none' : ''">
                                                    <img v-if="authUserForm.avatar_url" :src="resolveAvatarUrl(authUserForm.avatar_url)" class="w-full h-full object-cover" alt="Foto User" @error="authUserForm.avatar_url = null" />
                                                    <span v-else>{{ ((authUserForm.nama || authUserForm.username || 'U')[0] || 'U').toUpperCase() }}</span>
                                                    <div v-if="submittingAuthUserAvatar"
                                                        class="absolute inset-0 bg-slate-950/55 flex items-center justify-center">
                                                        <i class="fa-solid fa-spinner fa-spin text-white text-2xl"></i>
                                                    </div>
                                                </label>
                                                <input id="auth-user-avatar-input" name="auth_user_avatar_input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" :disabled="submittingAuthUserAvatar" @change="uploadAuthUserAvatar" />
                                                <label for="auth-user-avatar-input" class="text-body-sm text-slate-400 font-bold uppercase cursor-pointer hover:text-ppp-accent transition-colors"
                                                    :class="submittingAuthUserAvatar ? 'opacity-50 pointer-events-none' : ''">
                                                    <i class="fa-solid fa-upload mr-1" :class="submittingAuthUserAvatar ? 'fa-spin' : ''"></i>
                                                    <span v-if="submittingAuthUserAvatar">Mengunggah...</span>
                                                    <span v-else>Ganti Foto User</span>
                                                </label>
                                            </div>
                                            <div class="sr-only" aria-hidden="true">
                                                <label for="modal-auth-user-username-hint">Username</label>
                                                <input id="modal-auth-user-username-hint" type="text" name="username"
                                                    :value="currentUser?.username || ''" autocomplete="username" readonly />
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label for="auth-user-username"
                                                        class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Username <span class="text-danger">*</span></label>
                                                    <input id="auth-user-username" name="auth_user_username"
                                                        v-model="authUserForm.username" type="text" placeholder="mis. kasir"
                                                        autocomplete="off" minlength="3" class="form-input font-bold" />
                                                </div>
                                                <div>
                                                    <label for="auth-user-nama"
                                                        class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Nama <span class="text-danger">*</span></label>
                                                    <input id="auth-user-nama" name="auth_user_nama"
                                                        v-model="authUserForm.nama" type="text" placeholder="Nama lengkap"
                                                        autocomplete="off" class="form-input" />
                                                </div>
                                            </div>
                                            <div>
                                                <label for="auth-user-email"
                                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Email</label>
                                                    <input id="auth-user-email" name="auth_user_email"
                                                        v-model="authUserForm.email" type="email" placeholder="opsional"
                                                        autocomplete="off" class="form-input" />
                                            </div>
                                            <div class="relative search-select-container">
                                                <label for="auth-user-role"
                                                    class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Role <span class="text-danger">*</span></label>
                                                <div @click="toggleSearchSelect($event, 'authUserRole')"
                                                    class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form">
                                                    <span :class="authUserForm.role ? 'text-slate-800 font-medium' : 'text-slate-400'">
                                                        {{ optionLabel(authUserRoleOptions, authUserForm.role, 'Pilih Role') }}
                                                    </span>
                                                    <i class="fa-solid fa-chevron-down text-body-sm text-slate-300"></i>
                                                </div>
                                                <transition name="fade">
                                                    <div v-if="searchSelectOpen === 'authUserRole'" :style="popoverStyle"
                                                        class="search-select-popover">
                                                        <div class="relative mb-2">
                                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-body-sm"></i>
                                                            <input v-model="searchSelectQuery" type="text" name="auth_user_role_search"
                                                                autocomplete="off" aria-label="Cari role user" placeholder="Cari role..."
                                                                class="form-input-popover" @click.stop />
                                                        </div>
                                                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                            <div v-for="option in filteredAuthUserRoleOptions" :key="option.value"
                                                                @click="authUserForm.role = option.value; searchSelectOpen = null"
                                                                :class="['popover-option', authUserForm.role === option.value ? 'popover-option-active' : '']">
                                                                {{ option.label }}
                                                            </div>
                                                            <div v-if="filteredAuthUserRoleOptions.length === 0"
                                                                class="px-3 py-4 text-center text-body-sm text-slate-400 uppercase">
                                                                Tidak ditemukan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </transition>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label for="auth-user-pin"
                                                        class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Password <span class="text-danger">*</span></label>
                                                    <input id="auth-user-pin" name="auth_user_pin"
                                                        v-model="authUserForm.pin" type="password" :placeholder="authUserFormMode === 'edit' ? 'Kosongkan jika tidak diubah' : 'Password login'"
                                                        autocomplete="new-password" minlength="6" class="form-input" />
                                                </div>
                                                <div>
                                                    <label for="auth-user-confirm-pin"
                                                        class="type-body-sm font-bold text-slate-400 uppercase mb-1.5 pl-1">Konfirmasi <span class="text-danger">*</span></label>
                                                    <input id="auth-user-confirm-pin" name="auth_user_confirm_pin"
                                                        v-model="authUserForm.confirmPin" type="password" :placeholder="authUserFormMode === 'edit' ? 'Ulangi password baru' : 'Ulangi Password'"
                                                        autocomplete="new-password" minlength="6" class="form-input" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer-bar modal-footer-actions">
                                            <button type="button" @click="closeAuthUserModal"
                                                class="primary-cta-button primary-cta-button--neutral">Batal</button>
                                            <button type="submit" :disabled="submittingAuthUser"
                                                class="primary-cta-button primary-cta-button--info active:scale-95 disabled:opacity-50">
                                                <i v-if="submittingAuthUser" class="fa-solid fa-spinner fa-spin"></i>
                                                <i v-else :class="['fa-solid', authUserFormMode === 'edit' ? 'fa-floppy-disk' : 'fa-user-plus']"></i>
                                                {{ authUserFormMode === 'edit' ? 'Simpan Perubahan' : 'Simpan User' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </transition>
                    </teleport>
@endverbatim
