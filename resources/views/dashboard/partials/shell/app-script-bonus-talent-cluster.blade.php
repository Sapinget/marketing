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
                const pagedBonusRows = computed(() => filteredBonusRows.value.slice((bonusPage.value - 1) * PAGE_SIZE, bonusPage.value * PAGE_SIZE));

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

                const talentBonusRows = computed(() => {
                    const start = bonusFilter.value.start;
                    const end = bonusFilter.value.end;
                    const distByMaster = new Map();

                    distributionData.value.forEach((row) => {
                        const key = String(row.Master_ID || '');
                        if (!key) return;
                        if (!distByMaster.has(key)) distByMaster.set(key, []);
                        distByMaster.get(key).push(row);
                    });

                    return masterPlanData.value
                        .filter((item) => {
                            const talents = Array.isArray(item.TalentList) && item.TalentList.length
                                ? item.TalentList
                                : String(item.Talent || '').split(/[,;]/).map((name) => name.trim()).filter(Boolean);
                            const distRows = distByMaster.get(String(item.ID || '')) || [];
                            const rawDate = item.Tanggal_Rencana || '';
                            if (!talents.length || !rawDate) return false;
                            if (start && rawDate < start) return false;
                            if (end && rawDate > end) return false;
                            return true;
                        })
                        .flatMap((item) => {
                            const talents = Array.isArray(item.TalentList) && item.TalentList.length
                                ? item.TalentList
                                : String(item.Talent || '').split(/[,;]/).map((name) => name.trim()).filter(Boolean);
                            const distRows = distByMaster.get(String(item.ID || '')) || [];
                            const platforms = distRows.length
                                ? [...new Set(distRows.map((row) => String(row.Platform || '').trim()).filter(Boolean))]
                                : String(item.Platforms || '').split(/[;,]/).map((name) => name.trim()).filter(Boolean);
                            const rawDate = item.Tanggal_Rencana || '';
                            return talents.map((talent) => ({
                                id: item.ID,
                                Judul: item.Judul || 'Untitled',
                                Editor: item.Editor || '-',
                                Talent: talent,
                                TalentList: talents,
                                Platform: platforms.join(', ') || '-',
                                date: rawDate,
                                Views: 0,
                                Likes: 0,
                                Comments: 0,
                                Shares: 0,
                                Status: item.Status || '-',
                            }));
                        });
                });

                const buildTalentDailyRows = (rows) => {
                    const groupedByTalent = new Map();

                    rows.forEach((row) => {
                        const talentName = String(row.Talent || '-').trim() || '-';
                        const dateKey = String(row.date || '').slice(0, 10);
                        if (!dateKey) return;
                        if (!groupedByTalent.has(talentName)) groupedByTalent.set(talentName, new Map());
                        const byDate = groupedByTalent.get(talentName);
                        if (!byDate.has(dateKey)) byDate.set(dateKey, []);
                        byDate.get(dateKey).push(row);
                    });

                    const dailyRows = [];

                    [...groupedByTalent.entries()]
                        .sort((a, b) => a[0].localeCompare(b[0], 'id'))
                        .forEach(([talentName, byDate]) => {
                            let carryCount = 0;
                            [...byDate.entries()]
                                .sort((a, b) => a[0].localeCompare(b[0]))
                                .forEach(([dateKey, dayRows]) => {
                                    const effectiveCount = dayRows.length + carryCount;
                                    if (effectiveCount <= 0) return;

                                    const calculatedBonus = effectiveCount <= 2
                                        ? TALENT_DAILY_BONUS
                                        : Math.floor(effectiveCount / 2) * TALENT_DAILY_BONUS;

                                    carryCount = effectiveCount > 2 && effectiveCount % 2 === 1 ? 1 : 0;

                                    const totalViews = dayRows.reduce((sum, row) => sum + Number(row.Views || 0), 0);
                                    const totalLikes = dayRows.reduce((sum, row) => sum + Number(row.Likes || 0), 0);
                                    const totalComments = dayRows.reduce((sum, row) => sum + Number(row.Comments || 0), 0);
                                    const uniquePlatforms = [...new Set(dayRows.map((row) => row.Platform).filter(Boolean))];
                                    const titlePreview = dayRows.map((row) => row.Judul).filter(Boolean);
                                    const detailBits = [
                                        uniquePlatforms.length ? uniquePlatforms.join(', ') : '',
                                        titlePreview.length ? titlePreview.slice(0, 2).join(' | ') : '',
                                        titlePreview.length > 2 ? `+${titlePreview.length - 2} konten` : '',
                                        carryCount ? `carry ${carryCount} video` : ''
                                    ].filter(Boolean);

                                    dailyRows.push({
                                        id: `${talentName}-${dateKey}`,
                                        Talent: talentName,
                                        date: dateKey,
                                        videoCount: dayRows.length,
                                        effectiveCount,
                                        carriedToNextDay: carryCount,
                                        detailLabel: detailBits.join(' | '),
                                        Views: totalViews,
                                        Likes: totalLikes,
                                        Comments: totalComments,
                                        calculatedBonus
                                    });
                                });
                        });

                    return dailyRows.sort((a, b) => {
                        if (a.date !== b.date) return b.date.localeCompare(a.date);
                        return a.Talent.localeCompare(b.Talent, 'id');
                    });
                };

                const talentDashboardData = computed(() => {
                    const rows = buildTalentDailyRows(talentBonusRows.value);
                    const leaderboardMap = new Map();
                    rows.forEach((row) => {
                        const name = row.Talent || '-';
                        if (!leaderboardMap.has(name)) {
                            leaderboardMap.set(name, { name, count: 0, bonus: 0, views: 0, likes: 0, comments: 0 });
                        }
                        const current = leaderboardMap.get(name);
                        current.count++;
                        current.bonus += Number(row.calculatedBonus || 0);
                        current.views += Number(row.Views || 0);
                        current.likes += Number(row.Likes || 0);
                        current.comments += Number(row.Comments || 0);
                    });

                    const leaderboard = [...leaderboardMap.values()].sort((a, b) => b.bonus - a.bonus || b.views - a.views);

                    return {
                        rows,
                        leaderboard,
                        totalEntries: rows.length,
                        totalBonus: rows.reduce((sum, row) => sum + Number(row.calculatedBonus || 0), 0),
                        totalViews: rows.reduce((sum, row) => sum + Number(row.Views || 0), 0),
                        totalLikes: rows.reduce((sum, row) => sum + Number(row.Likes || 0), 0),
                        totalComments: rows.reduce((sum, row) => sum + Number(row.Comments || 0), 0),
                    };
                });

                const talentTotalPages = computed(() => Math.max(1, Math.ceil(talentDashboardData.value.rows.length / PAGE_SIZE)));
                const pagedTalentRows = computed(() => talentDashboardData.value.rows.slice((talentPage.value - 1) * PAGE_SIZE, talentPage.value * PAGE_SIZE));

                watch([bonusMonth, bonusYear], () => { talentPage.value = 1; });
@endverbatim
