@verbatim
                if (!window.MarketingDashboardRuntimeHelpers || !window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsActions) {
                    window.MarketingDashboardRuntimeHelpers = {
                        ...(window.MarketingDashboardRuntimeHelpers || {}),
                        createAdminUserSettingsActions: (deps) => {
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
                                if (!deps.authUserForm.value.username || !deps.authUserForm.value.nama || !deps.authUserForm.value.pin || !deps.authUserForm.value.confirmPin) {
                                    deps.showNotification("Lengkapi form user baru terlebih dahulu!");
                                    return;
                                }
                                if (deps.authUserForm.value.pin !== deps.authUserForm.value.confirmPin) {
                                    deps.showNotification("Konfirmasi PIN user baru tidak cocok!");
                                    return;
                                }
                                deps.submittingAuthUser.value = true;
                                deps.ensureRunApi().withSuccessHandler(() => {
                                    deps.submittingAuthUser.value = false;
                                    deps.authUserForm.value = { username: "", nama: "", email: "", pin: "", confirmPin: "" };
                                    loadAuthUsers();
                                    deps.showNotification("User baru berhasil dibuat!");
                                }).withFailureHandler((err) => {
                                    deps.submittingAuthUser.value = false;
                                    deps.notifyError('Gagal membuat user', err, 'User baru belum berhasil disimpan.');
                                }).createAuthUser({
                                    username: deps.authUserForm.value.username,
                                    nama: deps.authUserForm.value.nama,
                                    email: deps.authUserForm.value.email || null,
                                    pin: deps.authUserForm.value.pin,
                                    pin_confirmation: deps.authUserForm.value.confirmPin,
                                });
                            };
                            return { loadAuthUsers, loadActivityLogs, saveProfileInfo, saveProfileSetting, submitAuthUserForm };
                        },
                    };
                }

                const {
                    loadAuthUsers,
                    loadActivityLogs,
                    saveProfileInfo,
                    saveProfileSetting,
                    submitAuthUserForm,
                } = window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsActions({
                    ensureRunApi,
                    currentUser,
                    notifyError,
                    showNotification,
                    submittingInfo,
                    submittingPin,
                    profileForm,
                    submittingAuthUser,
                    authUsers,
                    authUsersLoaded,
                    activityLogs,
                    activityLogsLoaded,
                    activityLogFilters,
                    authUserForm,
                });

@endverbatim
