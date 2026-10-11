@verbatim
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

                watch(() => analyticsForm.value.ID_Post, (value) => {
                    queueAnalyticsIdPostSync(value);
                });

                const analyticsTotalPages = computed(() => Math.max(1, Math.ceil(filteredAnalyticsData.value.length / PAGE_SIZE)));

                const pagedAnalyticsData = computed(() => filteredAnalyticsData.value.slice((analyticsPage.value - 1) * PAGE_SIZE, analyticsPage.value * PAGE_SIZE));

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

                const analyticsSummary = computed(() => {
                    const d = filteredAnalyticsData.value || [];
                    const views = _sSum(d, r => r.Views), likes = _sSum(d, r => r.Likes), comments = _sSum(d, r => r.Comments);
                    const platV = {}; d.forEach(r => { const k = (r.Platform || '-'); platV[k] = (platV[k] || 0) + (Number(r.Views) || 0); });
                    const topPlatformViews = Object.entries(platV).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Views', value: formatNumber(views), icon: 'fa-eye', color: 'text-amber', subColor: 'text-amber', sub: formatNumber(d.length) + ' konten' },
                        { label: 'Total Likes', value: formatNumber(likes), icon: 'fa-heart', color: 'text-danger', subColor: 'text-danger', sub: 'Engagement' },
                        { label: 'Total Comments', value: formatNumber(comments), icon: 'fa-comment', color: 'text-success', subColor: 'text-success', sub: 'Interaksi' },
                        { label: 'Total Konten', value: formatNumber(d.length), unit: 'Post', icon: 'fa-photo-film', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Dianalisa' },
                        { label: 'Top Platform Views', value: topPlatformViews ? String(topPlatformViews[0]).toUpperCase() : '-', icon: 'fa-chart-simple', color: 'text-success', sub: formatNumber(topPlatformViews ? topPlatformViews[1] : 0) + ' views', subColor: 'text-success' },
                    ]) };
                });

                Object.assign(menuExports, {
                    analyticsTotalPages,
                    pagedAnalyticsData,
                    openAnalyticsModal,
                    analyticsSummary,
                
                    queueAnalyticsIdPostSync,
});
@endverbatim
