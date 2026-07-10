export function createAdminUserSettingsState(ref) {
    return {
        profileForm: ref({
            namaLengkap: '',
            oldPin: '',
            newPin: '',
            confirmPin: '',
        }),
        authUsers: ref([]),
        authUsersLoaded: ref(false),
        authUserSearchQuery: ref(''),
        activityLogs: ref([]),
        activityLogsLoaded: ref(false),
        activityLogFilters: ref({
            table_name: '',
            action: '',
            record_key: '',
        }),
        submittingAuthUser: ref(false),
        showAuthUserModal: ref(false),
        authUserFormMode: ref('create'),
        authUserForm: ref({
            ID: null,
            username: '',
            nama: '',
            email: '',
            role: 'operasional',
            pin: '',
            confirmPin: '',
        }),
    };
}

export function createAdminUserSettingsActions(deps) {
    const {
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
    } = deps;

    const isValidEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
    const buildEmptyAuthUserForm = () => ({
        ID: null,
        username: '',
        nama: '',
        email: '',
        role: 'operasional',
        pin: '',
        confirmPin: '',
    });

    const openAuthUserModal = (mode = 'create', user = null) => {
        authUserFormMode.value = mode === 'edit' ? 'edit' : 'create';
        authUserForm.value = mode === 'edit' && user
            ? {
                ID: user.ID ?? user.id ?? null,
                username: String(user.username || ''),
                nama: String(user.nama || user.username || ''),
                email: String(user.email || ''),
                role: String(user.role_key || 'operasional'),
                pin: '',
                confirmPin: '',
            }
            : buildEmptyAuthUserForm();
        showAuthUserModal.value = true;
    };

    const closeAuthUserModal = () => {
        showAuthUserModal.value = false;
    };

    const loadAuthUsers = () => {
        const runner = ensureRunApi();
        if (runner.isWebProxy && !currentUser.value) {
            authUsers.value = [];
            authUsersLoaded.value = false;
            return;
        }

        runner
            .withSuccessHandler((rows) => {
                authUsers.value = Array.isArray(rows) ? rows : [];
                authUsersLoaded.value = true;
            })
            .withFailureHandler((err) => {
                authUsers.value = [];
                authUsersLoaded.value = false;
                notifyError('Gagal memuat user', err, 'Daftar user belum berhasil dimuat.');
            })
            .getAuthUsers();
    };

    const loadActivityLogs = () => {
        const runner = ensureRunApi();
        if (runner.isWebProxy && !currentUser.value) {
            activityLogs.value = [];
            activityLogsLoaded.value = false;
            return;
        }

        runner
            .withSuccessHandler((rows) => {
                activityLogs.value = Array.isArray(rows) ? rows : [];
                activityLogsLoaded.value = true;
            })
            .withFailureHandler((err) => {
                activityLogs.value = [];
                activityLogsLoaded.value = false;
                notifyError('Gagal memuat activity logs', err, 'Riwayat aktivitas belum berhasil dimuat.');
            })
            .getActivityLogs({
                table_name: activityLogFilters.value.table_name || '',
                action: activityLogFilters.value.action || '',
                record_key: activityLogFilters.value.record_key || '',
            });
    };

    const saveProfileInfo = () => {
        if (!profileForm.value.namaLengkap) {
            showNotification('Nama Lengkap tidak boleh kosong!');
            return;
        }

        submittingInfo.value = true;
        ensureRunApi()
            .withSuccessHandler(() => {
                if (currentUser.value) {
                    currentUser.value.nama = profileForm.value.namaLengkap;
                    localStorage.setItem('ppp_user', JSON.stringify(currentUser.value));
                }
                submittingInfo.value = false;
                showNotification('Informasi Pribadi berhasil diupdate!');
            })
            .withFailureHandler((err) => {
                submittingInfo.value = false;
                notifyError('Gagal menyimpan', err, 'Informasi profil belum berhasil diperbarui.');
            })
            .updateUserNama(currentUser.value?.username, profileForm.value.namaLengkap);
    };

    const saveProfileSetting = () => {
        if (!profileForm.value.oldPin || !profileForm.value.newPin || !profileForm.value.confirmPin) {
            showNotification('Harap lengkapi semua field PIN!');
            return;
        }
        if (profileForm.value.newPin !== profileForm.value.confirmPin) {
            showNotification('Konfirmasi PIN baru tidak cocok!');
            return;
        }

        submittingPin.value = true;
        ensureRunApi()
            .withSuccessHandler(() => {
                submittingPin.value = false;
                showNotification('PIN berhasil diupdate!');
                profileForm.value.oldPin = '';
                profileForm.value.newPin = '';
                profileForm.value.confirmPin = '';
            })
            .withFailureHandler((err) => {
                submittingPin.value = false;
                notifyError('Gagal memperbarui PIN', err, 'PIN baru belum berhasil disimpan.');
            })
            .changePin(currentUser.value?.username, profileForm.value.oldPin, profileForm.value.newPin);
    };

    const submitAuthUserForm = () => {
        const formMode = authUserFormMode.value === 'edit' ? 'edit' : 'create';
        const userId = authUserForm.value.ID ?? null;
        const normalizedUsername = String(authUserForm.value.username || '').trim();
        const normalizedNama = String(authUserForm.value.nama || '').trim();
        const normalizedEmail = String(authUserForm.value.email || '').trim();
        const normalizedRole = String(authUserForm.value.role || 'operasional').trim() || 'operasional';
        const normalizedPin = String(authUserForm.value.pin || '');
        const normalizedConfirmPin = String(authUserForm.value.confirmPin || '');

        if (!normalizedUsername || !normalizedNama || (formMode === 'create' && (!normalizedPin || !normalizedConfirmPin))) {
            showNotification('Lengkapi form user baru terlebih dahulu!');
            return;
        }

        if (normalizedUsername.length < 3) {
            showNotification('Username minimal 3 karakter!', 'error');
            return;
        }

        if (normalizedEmail && !isValidEmail(normalizedEmail)) {
            showNotification('Format email tidak valid!', 'error');
            return;
        }

        if (formMode === 'create' && normalizedPin.length < 6) {
            showNotification('Password login minimal 6 karakter!', 'error');
            return;
        }

        if (formMode === 'edit' && normalizedPin && normalizedPin.length < 6) {
            showNotification('Password login minimal 6 karakter!', 'error');
            return;
        }

        if ((normalizedPin || normalizedConfirmPin) && normalizedPin !== normalizedConfirmPin) {
            showNotification('Konfirmasi password user baru tidak cocok!');
            return;
        }

        if (formMode === 'edit' && !userId) {
            showNotification('User yang akan diubah tidak valid.', 'error');
            return;
        }

        submittingAuthUser.value = true;

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

        const runner = ensureRunApi()
            .withSuccessHandler(() => {
                submittingAuthUser.value = false;
                authUserForm.value = buildEmptyAuthUserForm();
                showAuthUserModal.value = false;
                authUserFormMode.value = 'create';
                loadAuthUsers();
                showNotification(formMode === 'edit' ? 'User berhasil diperbarui!' : 'User baru berhasil dibuat!');
            })
            .withFailureHandler((err) => {
                submittingAuthUser.value = false;
                notifyError(
                    formMode === 'edit' ? 'Gagal memperbarui user' : 'Gagal membuat user',
                    err,
                    formMode === 'edit' ? 'Perubahan user belum berhasil disimpan.' : 'User baru belum berhasil disimpan.',
                );
            });

        if (formMode === 'edit') {
            runner.updateAuthUser(userId, payload);
            return;
        }

        runner.createAuthUser(payload);
    };

    const removeAuthUser = (user) => {
        const userId = user?.ID ?? user?.id ?? null;
        if (!userId) {
            showNotification('User yang akan dihapus tidak valid.', 'error');
            return;
        }

        showConfirm(
            'Hapus User?',
            `User @${String(user?.username || '-')} akan dihapus permanen.`,
            () => {
                ensureRunApi()
                    .withSuccessHandler(() => {
                        loadAuthUsers();
                        showNotification('User berhasil dihapus!');
                    })
                    .withFailureHandler((err) => {
                        notifyError('Gagal menghapus user', err, 'User belum berhasil dihapus.');
                    })
                    .deleteAuthUser(userId);
            },
        );
    };

    return {
        loadAuthUsers,
        loadActivityLogs,
        saveProfileInfo,
        saveProfileSetting,
        openAuthUserModal,
        closeAuthUserModal,
        submitAuthUserForm,
        removeAuthUser,
    };
}
