@verbatim
                const buildContentRanking = (mode) => {
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

                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;

                    const grouped = {};
                    analytics.forEach(aRow => {
                        const masterId = String(aRow.Master_ID || '');
                        const mRow = masterById.get(masterId);
                        const distRows = distByMaster.get(masterId) || [];
                        const dRow = distRows.find(r => (r.Platform || '').toLowerCase() === (aRow.Platform || '').toLowerCase()) || distRows[0] || null;
                        const rawDate = (dRow?.Tanggal_Publish) || (aRow.Tanggal_Post) || (aRow.Tanggal) || '';
                        if (start && rawDate && rawDate < start) return;
                        if (end && rawDate && rawDate > end) return;
                        const platform = (aRow.Platform || 'Lainnya').trim();
                        if (!grouped[platform]) grouped[platform] = [];
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);
                        const score = likes * 1 + comments * 3 + shares * 5;
                        grouped[platform].push({
                            title: mRow?.Judul || aRow.Judul || 'Untitled',
                            editor: mRow?.Editor || aRow.Editor || '-',
                            platform,
                            date: rawDate,
                            views, likes, comments, shares, score,
                            distributionLink: dRow?.Link || ''
                        });
                    });

                    return Object.keys(grouped).sort().map(platform => {
                        const rows = grouped[platform].sort((a, b) =>
                            mode === 'top'
                                ? (b.views - a.views || b.score - a.score)
                                : (a.views - b.views || a.score - b.score)
                        ).filter(r => mode === 'low' ? (r.views > 0 || r.score > 0) : true).slice(0, 5);
                        return { platform, rows };
                    }).filter(g => g.rows.length > 0);
                };

                const topContentByPlatform = computed(() => buildContentRanking('top'));
                const topContentCombined = computed(() =>
                    topContentByPlatform.value.flatMap(g => g.rows)
                        .sort((a, b) => b.views - a.views || b.score - a.score).slice(0, 5)
                );
                const topContentView = ref('all');

                const lowContentByPlatform = computed(() => buildContentRanking('low'));
                const lowContentCombined = computed(() =>
                    lowContentByPlatform.value.flatMap(g => g.rows)
                        .sort((a, b) => a.views - b.views || a.score - b.score).slice(0, 5)
                );
                const lowContentView = ref('all');

                watch(topContentByPlatform, (groups) => {
                    if (topContentView.value !== 'all' && !groups.some(group => group.platform === topContentView.value)) {
                        topContentView.value = 'all';
                    }
                });

                watch(lowContentByPlatform, (groups) => {
                    if (lowContentView.value !== 'all' && !groups.some(group => group.platform === lowContentView.value)) {
                        lowContentView.value = 'all';
                    }
                });

                // End Insight and Tren
@endverbatim
