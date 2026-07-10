@verbatim
                if (!window.MarketingDashboardRuntimeHelpers || !window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsActions) {
                    window.MarketingDashboardRuntimeHelpers = {
                        ...(window.MarketingDashboardRuntimeHelpers || {}),
                        createAdminUserSettingsActions: (deps) => {
                            const isValidEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || "").trim());
                            const buildEmptyAuthUserForm = () => ({ ID: null, username: "", nama: "", email: "", role: "operasional", pin: "", confirmPin: "" });
                            const loadAuthUsers = () => {
                                const runner = deps.ensureRunApi();
                                if (runner.isWebProxy && !deps.currentUser.value) {
                                    deps.authUsers.value = [];
                                    deps.authUsersLoaded.value = false;
                                    return;
                                }
                                runner.withSuccessHandler((rows) => {
                                    deps.authUsers.value = Array.isArray(rows) ? rows : [];
                                    deps.authUsersLoaded.value = true;
                                }).withFailureHandler((err) => {
                                    deps.authUsers.value = [];
                                    deps.authUsersLoaded.value = false;
                                    deps.notifyError('Gagal memuat user', err, 'Daftar user belum berhasil dimuat.');
                                }).getAuthUsers();
                            };
                            const loadActivityLogs = () => {
                                const runner = deps.ensureRunApi();
                                if (runner.isWebProxy && !deps.currentUser.value) {
                                    deps.activityLogs.value = [];
                                    deps.activityLogsLoaded.value = false;
                                    return;
                                }
                                runner.withSuccessHandler((rows) => {
                                    deps.activityLogs.value = Array.isArray(rows) ? rows : [];
                                    deps.activityLogsLoaded.value = true;
                                }).withFailureHandler((err) => {
                                    deps.activityLogs.value = [];
                                    deps.activityLogsLoaded.value = false;
                                    deps.notifyError('Gagal memuat activity logs', err, 'Riwayat aktivitas belum berhasil dimuat.');
                                }).getActivityLogs({
                                    table_name: deps.activityLogFilters.value.table_name || '',
                                    action: deps.activityLogFilters.value.action || '',
                                    record_key: deps.activityLogFilters.value.record_key || '',
                                });
                            };
                            const openAuthUserModal = (mode = "create", user = null) => {
                                deps.authUserFormMode.value = mode === "edit" ? "edit" : "create";
                                deps.authUserForm.value = mode === "edit" && user
                                    ? {
                                        ID: user.ID ?? user.id ?? null,
                                        username: String(user.username || ""),
                                        nama: String(user.nama || user.username || ""),
                                        email: String(user.email || ""),
                                        role: String(user.role_key || "operasional"),
                                        pin: "",
                                        confirmPin: "",
                                    }
                                    : buildEmptyAuthUserForm();
                                deps.showAuthUserModal.value = true;
                            };
                            const closeAuthUserModal = () => {
                                deps.showAuthUserModal.value = false;
                            };
                            const saveProfileInfo = () => {
                                if (!deps.profileForm.value.namaLengkap) {
                                    deps.showNotification("Nama Lengkap tidak boleh kosong!");
                                    return;
                                }
                                deps.submittingInfo.value = true;
                                deps.ensureRunApi().withSuccessHandler(() => {
                                    if (deps.currentUser.value) {
                                        deps.currentUser.value.nama = deps.profileForm.value.namaLengkap;
                                        localStorage.setItem("ppp_user", JSON.stringify(deps.currentUser.value));
                                    }
                                    deps.submittingInfo.value = false;
                                    deps.showNotification("Informasi Pribadi berhasil diupdate!");
                                }).withFailureHandler((err) => {
                                    deps.submittingInfo.value = false;
                                    deps.notifyError('Gagal menyimpan', err, 'Informasi profil belum berhasil diperbarui.');
                                }).updateUserNama(deps.currentUser.value?.username, deps.profileForm.value.namaLengkap);
                            };
                            const saveProfileSetting = () => {
                                if (!deps.profileForm.value.oldPin || !deps.profileForm.value.newPin || !deps.profileForm.value.confirmPin) {
                                    deps.showNotification("Harap lengkapi semua field PIN!");
                                    return;
                                }
                                if (deps.profileForm.value.newPin !== deps.profileForm.value.confirmPin) {
                                    deps.showNotification("Konfirmasi PIN baru tidak cocok!");
                                    return;
                                }
                                deps.submittingPin.value = true;
                                deps.ensureRunApi().withSuccessHandler(() => {
                                    deps.submittingPin.value = false;
                                    deps.showNotification("PIN berhasil diupdate!");
                                    deps.profileForm.value.oldPin = "";
                                    deps.profileForm.value.newPin = "";
                                    deps.profileForm.value.confirmPin = "";
                                }).withFailureHandler((err) => {
                                    deps.submittingPin.value = false;
                                    deps.notifyError('Gagal memperbarui PIN', err, 'PIN baru belum berhasil disimpan.');
                                }).changePin(deps.currentUser.value?.username, deps.profileForm.value.oldPin, deps.profileForm.value.newPin);
                            };
                            const submitAuthUserForm = () => {
                                const formMode = deps.authUserFormMode.value === "edit" ? "edit" : "create";
                                const userId = deps.authUserForm.value.ID ?? null;
                                const normalizedUsername = String(deps.authUserForm.value.username || "").trim();
                                const normalizedNama = String(deps.authUserForm.value.nama || "").trim();
                                const normalizedEmail = String(deps.authUserForm.value.email || "").trim();
                                const normalizedRole = String(deps.authUserForm.value.role || "operasional").trim() || "operasional";
                                const normalizedPin = String(deps.authUserForm.value.pin || "");
                                const normalizedConfirmPin = String(deps.authUserForm.value.confirmPin || "");

                                if (!normalizedUsername || !normalizedNama || (formMode === "create" && (!normalizedPin || !normalizedConfirmPin))) {
                                    deps.showNotification("Lengkapi form user baru terlebih dahulu!");
                                    return;
                                }

                                if (normalizedUsername.length < 3) {
                                    deps.showNotification("Username minimal 3 karakter!", "error");
                                    return;
                                }

                                if (normalizedEmail && !isValidEmail(normalizedEmail)) {
                                    deps.showNotification("Format email tidak valid!", "error");
                                    return;
                                }

                                if (formMode === "create" && normalizedPin.length < 6) {
                                    deps.showNotification("Password login minimal 6 karakter!", "error");
                                    return;
                                }

                                if (formMode === "edit" && normalizedPin && normalizedPin.length < 6) {
                                    deps.showNotification("Password login minimal 6 karakter!", "error");
                                    return;
                                }

                                if ((normalizedPin || normalizedConfirmPin) && normalizedPin !== normalizedConfirmPin) {
                                    deps.showNotification("Konfirmasi PIN user baru tidak cocok!");
                                    return;
                                }

                                if (formMode === "edit" && !userId) {
                                    deps.showNotification("User yang akan diubah tidak valid.", "error");
                                    return;
                                }

                                deps.submittingAuthUser.value = true;
                                const payload = {
                                    username: normalizedUsername,
                                    nama: normalizedNama,
                                    email: normalizedEmail || null,
                                    role: normalizedRole,
                                    ...(normalizedPin ? {
                                        pin: normalizedPin,
                                        pin_confirmation: normalizedConfirmPin,
                                    } : {}),
                                };
                                const runner = deps.ensureRunApi().withSuccessHandler(() => {
                                    deps.submittingAuthUser.value = false;
                                    deps.authUserForm.value = buildEmptyAuthUserForm();
                                    deps.authUserFormMode.value = "create";
                                    deps.showAuthUserModal.value = false;
                                    loadAuthUsers();
                                    deps.showNotification(formMode === "edit" ? "User berhasil diperbarui!" : "User baru berhasil dibuat!");
                                }).withFailureHandler((err) => {
                                    deps.submittingAuthUser.value = false;
                                    deps.notifyError(
                                        formMode === "edit" ? "Gagal memperbarui user" : "Gagal membuat user",
                                        err,
                                        formMode === "edit" ? "Perubahan user belum berhasil disimpan." : "User baru belum berhasil disimpan."
                                    );
                                });

                                if (formMode === "edit") {
                                    runner.updateAuthUser(userId, payload);
                                    return;
                                }

                                runner.createAuthUser(payload);
                            };
                            const removeAuthUser = (user) => {
                                const userId = user?.ID ?? user?.id ?? null;
                                if (!userId) {
                                    deps.showNotification("User yang akan dihapus tidak valid.", "error");
                                    return;
                                }
                                deps.showConfirm(
                                    "Hapus User?",
                                    `User @${String(user?.username || "-")} akan dihapus permanen.`,
                                    () => {
                                        deps.ensureRunApi()
                                            .withSuccessHandler(() => {
                                                loadAuthUsers();
                                                deps.showNotification("User berhasil dihapus!");
                                            })
                                            .withFailureHandler((err) => {
                                                deps.notifyError('Gagal menghapus user', err, 'User belum berhasil dihapus.');
                                            })
                                            .deleteAuthUser(userId);
                                    }
                                );
                            };
                            return { loadAuthUsers, loadActivityLogs, openAuthUserModal, closeAuthUserModal, saveProfileInfo, saveProfileSetting, submitAuthUserForm, removeAuthUser };
                        },
                    };
                }

                const {
                    loadAuthUsers,
                    loadActivityLogs,
                    openAuthUserModal,
                    closeAuthUserModal,
                    saveProfileInfo,
                    saveProfileSetting,
                    submitAuthUserForm,
                    removeAuthUser,
                } = window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsActions({
                    ensureRunApi,
                    currentUser,
                    notifyError,
                    showNotification,
                    submittingInfo,
                    submittingPin,
                    profileForm,
                    submittingAuthUser,
                    showAuthUserModal,
                    authUserFormMode,
                    authUsers,
                    authUsersLoaded,
                    activityLogs,
                    activityLogsLoaded,
                    activityLogFilters,
                    authUserForm,
                    showConfirm,
                });

@endverbatim
