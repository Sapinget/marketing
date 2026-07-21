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

                const analyticsSyncingMetaPost = ref(false);
                const suppressAnalyticsIdPostWatcher = ref(false);
                const analyticsOriginalMetrics = ref({ Views: 0, Likes: 0, Comments: 0, Shares: 0 });
                let analyticsIdPostLookupToken = 0;
                let analyticsIdPostDebounce = null;
                const normalizeMetaPostId = (value) => String(value || '').trim();
                const isInstagramAnalyticsPlatform = () => String(analyticsForm.value.Platform || '').trim().toLowerCase() === 'instagram';
                const getMetaFeedPostId = (row) => normalizeMetaPostId(row?.post_id || row?.ID || row?.Post_ID || row?.ID_Post || row?.id_post || row?.['Post ID'] || row?.['post id'] || row?.media_id);
                const restoreAnalyticsOriginalMetrics = () => {
                    analyticsForm.value.Views = Number(analyticsOriginalMetrics.value.Views || 0);
                    analyticsForm.value.Likes = Number(analyticsOriginalMetrics.value.Likes || 0);
                    analyticsForm.value.Comments = Number(analyticsOriginalMetrics.value.Comments || 0);
                    analyticsForm.value.Shares = Number(analyticsOriginalMetrics.value.Shares || 0);
                };
                const updateAnalyticsMetricInputs = () => {
                    [
                        ['analytics-views', analyticsForm.value.Views],
                        ['analytics-likes', analyticsForm.value.Likes],
                        ['analytics-comments', analyticsForm.value.Comments],
                        ['analytics-shares', analyticsForm.value.Shares],
                    ].forEach(([id, value]) => {
                        const input = document.getElementById(id);
                        if (input) input.value = Number(value || 0);
                    });
                };
                const applyAnalyticsMetaPost = (post) => {
                    analyticsForm.value.Views = Number(post?.views || 0);
                    analyticsForm.value.Likes = Number(post?.likes || 0);
                    analyticsForm.value.Comments = Number(post?.comments || 0);
                    analyticsForm.value.Shares = Number(post?.shares || 0);
                    updateAnalyticsMetricInputs();
                };
                const findMetaFeedPost = async (postId) => {
                    const normalizedPostId = normalizeMetaPostId(postId);
                    if (!normalizedPostId) return null;
                    const currentRows = Array.isArray(metaFeedData.value) ? metaFeedData.value : [];
                    let found = currentRows.find((row) => getMetaFeedPostId(row) === normalizedPostId) || null;
                    if (found) return found;
                    const rows = await ensureRunApi().getMetaFeedData();
                    metaFeedData.value = Array.isArray(rows) ? rows : [];
                    return metaFeedData.value.find((row) => getMetaFeedPostId(row) === normalizedPostId) || null;
                };
                const syncAnalyticsFormFromIdPost = async (postId) => {
                    const normalizedPostId = normalizeMetaPostId(postId);
                    const lookupToken = ++analyticsIdPostLookupToken;
                    if (!normalizedPostId) return;
                    analyticsSyncingMetaPost.value = true;
                    try {
                        const post = await findMetaFeedPost(normalizedPostId);
                        if (lookupToken !== analyticsIdPostLookupToken || normalizeMetaPostId(analyticsForm.value.ID_Post) !== normalizedPostId) return;
                        if (!post) {
                            showNotification('ID Post tidak ditemukan di Feed Konten.');
                            return;
                        }
                        applyAnalyticsMetaPost(post);
                        showNotification('Metric otomatis diambil dari Feed Konten.');
                    } catch (error) {
                        if (lookupToken !== analyticsIdPostLookupToken) return;
                        handleError(error);
                    } finally {
                        if (lookupToken === analyticsIdPostLookupToken) analyticsSyncingMetaPost.value = false;
                    }
                };
                const queueAnalyticsIdPostSync = (value, options = {}) => {
                    if (!analyticsModalOpen.value || (!options.force && suppressAnalyticsIdPostWatcher.value)) return;
                    if (!isInstagramAnalyticsPlatform()) return;
                    if (analyticsIdPostDebounce) clearTimeout(analyticsIdPostDebounce);
                    const normalizedValue = normalizeMetaPostId(value);
                    if (!normalizedValue) {
                        analyticsIdPostLookupToken++;
                        restoreAnalyticsOriginalMetrics();
                        analyticsSyncingMetaPost.value = false;
                        return;
                    }
                    const localMatch = (Array.isArray(metaFeedData.value) ? metaFeedData.value : []).some((row) => getMetaFeedPostId(row) === normalizedValue);
                    const delay = localMatch ? 0 : 250;
                    analyticsIdPostDebounce = setTimeout(() => syncAnalyticsFormFromIdPost(normalizedValue), delay);
                };
                const bindAnalyticsIdPostDomSync = () => {
                    const input = document.getElementById('analytics-id-post');
                    if (!input || input.__analyticsIdPostSyncBound) return;
                    input.__analyticsIdPostSyncBound = true;
                    const syncFromDomInput = async () => {
                        analyticsForm.value.ID_Post = input.value;
                        if (!isInstagramAnalyticsPlatform()) return;
                        if (analyticsIdPostDebounce) clearTimeout(analyticsIdPostDebounce);
                        const normalizedValue = normalizeMetaPostId(input.value);
                        if (!normalizedValue) {
                            analyticsIdPostLookupToken++;
                            restoreAnalyticsOriginalMetrics();
                            updateAnalyticsMetricInputs();
                            analyticsSyncingMetaPost.value = false;
                            return;
                        }
                        const localMatch = (Array.isArray(metaFeedData.value) ? metaFeedData.value : []).some((row) => getMetaFeedPostId(row) === normalizedValue);
                        const delay = localMatch ? 0 : 250;
                        analyticsIdPostDebounce = setTimeout(async () => {
                            const lookupToken = ++analyticsIdPostLookupToken;
                            analyticsSyncingMetaPost.value = true;
                            try {
                                const response = await fetch(resolveAppUrl('/api/meta-posts/feed'), {
                                    headers: { 'Accept': 'application/json' },
                                });
                                const payload = response.ok ? await response.json() : { data: [] };
                                const rows = Array.isArray(payload.data) ? payload.data : [];
                                metaFeedData.value = rows;
                                const post = rows.find((row) => getMetaFeedPostId(row) === normalizedValue) || null;
                                if (lookupToken !== analyticsIdPostLookupToken || normalizeMetaPostId(input.value) !== normalizedValue) return;
                                if (!post) {
                                    showNotification('ID Post tidak ditemukan di Feed Konten.');
                                    return;
                                }
                                applyAnalyticsMetaPost(post);
                                showNotification('Metric otomatis diambil dari Feed Konten.');
                            } catch (error) {
                                if (lookupToken !== analyticsIdPostLookupToken) return;
                                handleError(error);
                            } finally {
                                if (lookupToken === analyticsIdPostLookupToken) analyticsSyncingMetaPost.value = false;
                            }
                        }, delay);
                    };
                    input.addEventListener('input', syncFromDomInput);
                    input.addEventListener('change', syncFromDomInput);
                };
                const openAnalyticsModal = (item = null) => {
                    analyticsIdPostLookupToken++;
                    analyticsSyncingMetaPost.value = false;
                    if (item) {
                        analyticsForm.value = { ...item };
                        modalType.value = "edit";
                    } else {
                        analyticsForm.value = {
                            ID: null,
                            Master_ID: "",
                            Judul: "",
                            Platform: "Instagram",
                            ID_Post: "",
                            Views: 0,
                            Likes: 0,
                            Comments: 0,
                            Shares: 0
                        };
                        modalType.value = "create";
                    }
                    analyticsOriginalMetrics.value = {
                        Views: analyticsForm.value.Views,
                        Likes: analyticsForm.value.Likes,
                        Comments: analyticsForm.value.Comments,
                        Shares: analyticsForm.value.Shares,
                    };
                    analyticsModalOpen.value = true;
                    suppressAnalyticsIdPostWatcher.value = true;
                    nextTick(() => {
                        suppressAnalyticsIdPostWatcher.value = false;
                        bindAnalyticsIdPostDomSync();
                    });
                };
                watch(() => analyticsForm.value.ID_Post, (value) => {
                    queueAnalyticsIdPostSync(value);
                });

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
