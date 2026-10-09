@verbatim
                // Bonus Report computed & methods

                const formatCurrency = window.MarketingDashboardRuntimeHelpers?.formatCurrency || ((v) => {
                    if (v == null || isNaN(v)) return 'Rp 0';
                    return `Rp ${Number(v).toLocaleString('id-ID')}`;
                });

                const getBonusTier = (views, isColab) => {
                    const src = isColab ? bonusConfig.value.reelsColab : bonusConfig.value.reelsNonColab;
                    const tiers = Array.isArray(src) ? [...src] : [];
                    tiers.sort((a, b) => b.min - a.min);
                    const tier = tiers.find(t => views >= t.min);
                    return tier ? tier.amount : 0;
                };

                const filteredBonusRows = computed(() => {
                    const analytics = analyticsData.value;
                    const master = masterPlanData.value;
                    const dist = distributionData.value;
                    if (!analytics.length) return [];

                    const masterById = new Map(master.map(m => [String(m.ID || ''), m]));
                    const distByMaster = new Map();
                    dist.forEach(d => {
                        const key = String(d.Master_ID || '');
                        if (!key) return;
                        if (!distByMaster.has(key)) distByMaster.set(key, []);
                        distByMaster.get(key).push(d);
                    });

                    const start = bonusFilter.value.start;
                    const end = bonusFilter.value.end;
                    const cfg = bonusConfig.value;
                    if (!cfg?.engagement) return [];

                    return analytics.map(aRow => {
                        const masterId = String(aRow.Master_ID || '');
                        const mRow = masterById.get(masterId) || {};
                        const distRows = distByMaster.get(masterId) || [];
                        const dRow = distRows.find(r => (r.Platform || '').toLowerCase() === (aRow.Platform || '').toLowerCase()) || distRows[0] || null;
                        const analyticsDate = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';
                        const rawDate = dRow?.Tanggal_Publish || analyticsDate;

                        if (start && rawDate && rawDate < start) return null;
                        if (end && rawDate && rawDate > end) return null;

                        const platform = (aRow.Platform || '').toLowerCase();
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);

                        if ((mRow.Format_Konten || '').toUpperCase() === 'IKLAN') return null;

                        const colab = mRow.Colab || '';
                        const isColab = colab && colab.toLowerCase() !== 'tidak' && colab.trim() !== '';
                        const contentType = isColab ? 'Colab' : (mRow.Format_Konten || 'Regular');

                        const viewBonus = getBonusTier(views, isColab);

                        const safeFloor = (val, unit, bonus) => (unit > 0 && val >= unit ? bonus : 0);
                        let likeBonus = 0;
                        if (platform.includes('instagram')) {
                            likeBonus = safeFloor(likes, cfg.engagement.instagram.likeUnit, cfg.engagement.instagram.likeBonus);
                        } else if (platform.includes('tiktok')) {
                            likeBonus = safeFloor(likes, cfg.engagement.tiktok.likeUnit, cfg.engagement.tiktok.likeBonus);
                        } else if (platform.includes('youtube')) {
                            likeBonus = safeFloor(likes, cfg.engagement.youtube.likeUnit, cfg.engagement.youtube.likeBonus);
                        }

                        const commentBonus = safeFloor(comments, cfg.engagement.general.commentUnit, cfg.engagement.general.commentBonus);
                        const calculatedBonus = viewBonus + likeBonus + commentBonus;

                        return {
                            id: aRow.ID,
                            masterPlan: mRow,
                            Judul: mRow.Judul || aRow.Judul || 'Untitled',
                            Editor: mRow.Editor || aRow.Editor || '-',
                            Talent: mRow.Talent || '',
                            TalentList: Array.isArray(mRow.TalentList) ? mRow.TalentList : [],
                            Platform: aRow.Platform || '-',
                            date: rawDate,
                            Views: views, Likes: likes, Comments: comments, Shares: shares,
                            contentType,
                            viewBonus, likeBonus, commentBonus, calculatedBonus
                        };
                    }).filter(r => r && r.calculatedBonus > 0);
                });

                const bonusTotal = computed(() => {
                    const rows = filteredBonusRows.value;
                    return {
                        count: rows.length,
                        views: rows.reduce((s, r) => s + r.Views, 0),
                        likes: rows.reduce((s, r) => s + r.Likes, 0),
                        comments: rows.reduce((s, r) => s + r.Comments, 0),
                        totalMoney: rows.reduce((s, r) => s + r.calculatedBonus, 0)
                    };
                });

                const bonusTotalPages = computed(() => Math.max(1, Math.ceil(filteredBonusRows.value.length / PAGE_SIZE)));

                watch([bonusMonth, bonusYear], () => { bonusPage.value = 1; });
                watch([() => filteredBonusRows.value.length, bonusTotalPages], ([rowCount, totalPages]) => {
                    if (rowCount === 0) {
                        bonusPage.value = 1;
                        return;
                    }
                    if (bonusPage.value > totalPages) bonusPage.value = totalPages;
                    if (bonusPage.value < 1) bonusPage.value = 1;
                }, { immediate: true });

                const TALENT_DAILY_BONUS = 150000;

                watch([bonusMonth, bonusYear], () => { talentPage.value = 1; });
@endverbatim
