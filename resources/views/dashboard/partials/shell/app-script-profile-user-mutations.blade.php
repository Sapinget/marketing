@verbatim
                const saveProfileInfo = () => {
                    if (!profileForm.value.namaLengkap) {
                        showNotification("Nama Lengkap tidak boleh kosong!");
                        return;
                    }
                    submittingInfo.value = true;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            if (currentUser.value) {
                                currentUser.value.nama = profileForm.value.namaLengkap;
                                localStorage.setItem("ppp_user", JSON.stringify(currentUser.value));
                            }
                            submittingInfo.value = false;
                            showNotification("Informasi Pribadi berhasil diupdate!");
                        })
                        .withFailureHandler((err) => {
                            submittingInfo.value = false;
                            notifyError('Gagal menyimpan', err, 'Informasi profil belum berhasil diperbarui.');
                        })
                        .updateUserNama(currentUser.value?.username, profileForm.value.namaLengkap);
                };

                const saveProfileSetting = () => {
                    if (!profileForm.value.oldPin || !profileForm.value.newPin || !profileForm.value.confirmPin) {
                        showNotification("Harap lengkapi semua field PIN!");
                        return;
                    }
                    if (profileForm.value.newPin !== profileForm.value.confirmPin) {
                        showNotification("Konfirmasi PIN baru tidak cocok!");
                        return;
                    }
                    submittingPin.value = true;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            submittingPin.value = false;
                            showNotification("PIN berhasil diupdate!");
                            profileForm.value.oldPin = "";
                            profileForm.value.newPin = "";
                            profileForm.value.confirmPin = "";
                        })
                        .withFailureHandler((err) => {
                            submittingPin.value = false;
                            notifyError('Gagal memperbarui PIN', err, 'PIN baru belum berhasil disimpan.');
                        })
                        .changePin(currentUser.value?.username, profileForm.value.oldPin, profileForm.value.newPin);
                };

                const submitAuthUserForm = () => {
                    if (!authUserForm.value.username || !authUserForm.value.nama || !authUserForm.value.pin || !authUserForm.value.confirmPin) {
                        showNotification("Lengkapi form user baru terlebih dahulu!");
                        return;
                    }
                    if (authUserForm.value.pin !== authUserForm.value.confirmPin) {
                        showNotification("Konfirmasi PIN user baru tidak cocok!");
                        return;
                    }

                    submittingAuthUser.value = true;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            submittingAuthUser.value = false;
                            authUserForm.value = {
                                username: "",
                                nama: "",
                                email: "",
                                pin: "",
                                confirmPin: ""
                            };
                            loadAuthUsers();
                            showNotification("User baru berhasil dibuat!");
                        })
                        .withFailureHandler((err) => {
                            submittingAuthUser.value = false;
                            notifyError('Gagal membuat user', err, 'User baru belum berhasil disimpan.');
                        })
                        .createAuthUser({
                            username: authUserForm.value.username,
                            nama: authUserForm.value.nama,
                            email: authUserForm.value.email || null,
                            pin: authUserForm.value.pin,
                            pin_confirmation: authUserForm.value.confirmPin,
                        });
                };

@endverbatim
