@verbatim
                const loadAnalyticsData = async () => {
                    if (ensureRunApi().isWebProxy) {
                        try {
                            const response = await fetch(resolveAppUrl('/api/analytics'), {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            const payload = await response.json();
                            analyticsData.value = Array.isArray(payload.data) ? payload.data : [];
                            return;
                        } catch (error) { }
                    }
                    return new Promise((resolve, reject) => {
                        ensureRunApi().withSuccessHandler((data) => {
                            analyticsData.value = Array.isArray(data) ? data : [];
                            resolve();
                        }).withFailureHandler(reject).getAnalyticsData();
                    });
                };

                const loadDistributionData = async () => {
                    if (ensureRunApi().isWebProxy) {
                        try {
                            const response = await fetch(resolveAppUrl('/api/distributions'), {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!response.ok) throw new Error(`HTTP ${response.status}`);
                            const payload = await response.json();
                            distributionData.value = Array.isArray(payload.data) ? payload.data : [];
                            return;
                        } catch (error) { }
                    }
                    return new Promise((resolve, reject) => {
                        ensureRunApi().withSuccessHandler((data) => {
                            distributionData.value = Array.isArray(data) ? data : [];
                            resolve();
                        }).withFailureHandler(reject).getDistributionData();
                    });
                };

                const openDistModal = (item = null) => {
                    if (item) {
                        distributionForm.value = { ...item };
                        modalType.value = "edit";
                    } else {
                        distributionForm.value = {
                            ID: null,
                            Master_ID: "",
                            Judul: "",
                            Platform: "Instagram",
                            Tanggal_Publish: todayStr(),
                            Link: ""
                        };
                        modalType.value = "create";
                    }
                    distModalOpen.value = true;
                };

                const saveDistribution = () => {
                    if (submitting.value) return;
                    if (!distributionForm.value.Judul) {
                        showNotification("Judul wajib diisi");
                        return;
                    }
                    const isEdit = !!distributionForm.value.ID;
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            distModalOpen.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                if (isEdit) {
                                    const idx = distributionData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) distributionData.value[idx] = { ...distributionData.value[idx], ...saved };
                                } else {
                                    distributionData.value = [saved, ...distributionData.value];
                                }
                            } else {
                                loadDistributionData();
                            }
                            showNotification("Berhasil menyimpan data distribusi");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveDistribution(distributionForm.value);
                };

                const deleteDistribution = (id) => {
                    showConfirm("Hapus Data?", "Yakin ingin menghapus data distribusi ini?", () => {
                        ensureRunApi()
                            .withSuccessHandler(() => {
                                distributionData.value = distributionData.value.filter(r => r.ID !== id);
                                showNotification("Data berhasil dihapus");
                            })
                            .withFailureHandler(handleError)
                            .deleteDistribution(id);
                    });
                };

                const openAnalyticsModal = (item = null) => {
                    if (item) {
                        analyticsForm.value = { ...item };
                        modalType.value = "edit";
                    } else {
                        analyticsForm.value = {
                            ID: null,
                            Master_ID: "",
                            Judul: "",
                            Platform: "Instagram",
                            Views: 0,
                            Likes: 0,
                            Comments: 0,
                            Shares: 0
                        };
                        modalType.value = "create";
                    }
                    analyticsModalOpen.value = true;
                };

                const saveAnalytics = () => {
                    if (submitting.value) return;
                    if (!analyticsForm.value.Judul) {
                        showNotification("Judul wajib diisi");
                        return;
                    }
                    const isEdit = !!analyticsForm.value.ID;
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            analyticsModalOpen.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                if (isEdit) {
                                    const idx = analyticsData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) analyticsData.value[idx] = { ...analyticsData.value[idx], ...saved };
                                } else {
                                    analyticsData.value = [saved, ...analyticsData.value];
                                }
                            } else {
                                loadAnalyticsData();
                            }
                            showNotification("Berhasil menyimpan data analitik");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveAnalytics(analyticsForm.value);
                };

                const deleteAnalytics = (id) => {
                    showConfirm("Hapus Data?", "Yakin ingin menghapus data analitik ini?", () => {
                        ensureRunApi()
                            .withSuccessHandler(() => {
                                analyticsData.value = analyticsData.value.filter(r => r.ID !== id);
                                showNotification("Data berhasil dihapus");
                            })
                            .withFailureHandler(handleError)
                            .deleteAnalytics(id);
                    });
                };
@endverbatim
