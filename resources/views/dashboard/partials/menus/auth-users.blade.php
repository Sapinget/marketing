@verbatim
<div v-if="activeTab === 'auth_users'" class="space-y-6 animate-fadeIn pb-10">
                        <section class="section-card section-card-body">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                                        <i class="fa-solid fa-users-gear text-lg"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">Manajemen User</h2>
                                        <p class="type-body text-slate-500">Tambah dan lihat akun dashboard tanpa terminal.</p>
                                    </div>
                                </div>
                                <button @click="loadAuthUsers"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-600 hover:border-slate-300 hover:text-slate-900 transition-colors">
                                    <i class="fa-solid fa-rotate-right"></i>
                                    Refresh
                                </button>
                            </div>
                        </section>

                        <section class="bg-white radius-dialog border border-slate-100 p-6 md:p-8 shadow-sm">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                <div class="lg:col-span-5 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Username</label>
                                            <input v-model="authUserForm.username" type="text" placeholder="mis. kasir"
                                                class="form-input font-bold" />
                                        </div>
                                        <div>
                                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Nama</label>
                                            <input v-model="authUserForm.nama" type="text" placeholder="Nama user"
                                                class="form-input font-bold" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Email</label>
                                        <input v-model="authUserForm.email" type="email" placeholder="opsional"
                                            class="form-input" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">PIN</label>
                                            <input v-model="authUserForm.pin" type="password" placeholder="PIN login"
                                                class="form-input" />
                                        </div>
                                        <div>
                                            <label class="type-meta font-bold text-slate-400 uppercase tracking-widest mb-1.5 pl-1">Konfirmasi PIN</label>
                                            <input v-model="authUserForm.confirmPin" type="password" placeholder="Ulangi PIN"
                                                class="form-input" />
                                        </div>
                                    </div>
                                    <button @click="submitAuthUserForm" :disabled="submittingAuthUser"
                                        class="modal-primary-button w-full modal-primary-button--info shadow-lg shadow-blue-100 active:scale-95 disabled:opacity-50">
                                        <i v-if="submittingAuthUser" class="fa-solid fa-spinner fa-spin"></i>
                                        <i v-else class="fa-solid fa-user-plus"></i>
                                        Simpan User
                                    </button>
                                </div>

                                <div class="lg:col-span-7">
                                    <div class="overflow-hidden rounded-3xl border border-slate-100">
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
                                                <tr v-for="user in authUsers" :key="user.id">
                                                    <td class="px-4 py-3 text-[11px] font-bold text-slate-800">{{ user.username }}</td>
                                                    <td class="px-4 py-3 text-[11px] text-slate-600">{{ user.nama }}</td>
                                                    <td class="px-4 py-3 text-[11px] text-slate-500">{{ user.email || '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
@endverbatim
