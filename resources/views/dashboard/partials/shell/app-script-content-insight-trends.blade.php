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
                            driveLink: mRow?.Link_Drive || ''
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
                const lowContentByPlatform = computed(() => buildContentRanking('low'));
                const lowContentCombined = computed(() =>
                    lowContentByPlatform.value.flatMap(g => g.rows)
                        .sort((a, b) => a.views - b.views || a.score - b.score).slice(0, 5)
                );

                // Insight and Tren
                const analisaInsightTab = ref('konten');

                const colabVsNonColabStats = computed(() => {
                    const analytics = analyticsData.value;
                    const master = masterPlanData.value;
                    if (!analytics.length) return null;
                    const masterById = new Map(master.map(m => [String(m.ID || ''), m]));
                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;
                    const groups = { colab: [], nonColab: [] };
                    analytics.forEach(aRow => {
                        const rawDate = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';
                        if (start && rawDate && rawDate < start) return;
                        if (end && rawDate && rawDate > end) return;
                        const mRow = masterById.get(String(aRow.Master_ID || '')) || {};
                        const colab = (mRow.Colab || '').trim();
                        const isColab = colab && colab.toLowerCase() !== 'tidak';
                        const views = Number(aRow.Views || 0);
                        const likes = Number(aRow.Likes || 0);
                        const comments = Number(aRow.Comments || 0);
                        const shares = Number(aRow.Shares || 0);
                        const score = likes + comments * 3 + shares * 5;
                        (isColab ? groups.colab : groups.nonColab).push({ views, likes, comments, shares, score });
                    });
                    const summarize = (arr) => {
                        if (!arr.length) return { count: 0, avgViews: 0, avgLikes: 0, avgComments: 0, avgScore: 0 };
                        const n = arr.length;
                        return {
                            count: n,
                            avgViews: Math.round(arr.reduce((s, r) => s + r.views, 0) / n),
                            avgLikes: Math.round(arr.reduce((s, r) => s + r.likes, 0) / n),
                            avgComments: Math.round(arr.reduce((s, r) => s + r.comments, 0) / n),
                            avgScore: Math.round(arr.reduce((s, r) => s + r.score, 0) / n),
                        };
                    };
                    return { colab: summarize(groups.colab), nonColab: summarize(groups.nonColab) };
                });

                // Insight & Tren memakai date filter sendiri (default 26-25).
                const _insightInRange = (dateStr) => {
                    const f = insightDateFilter.value;
                    if (!f || !f.start) return true;
                    return isDateInRange(dateStr, f.start, f.end || f.start);
                };

                const contentFunnelStats = computed(() => {
                    const stageOrder = ['IDE', 'SHOOTING', 'EDITING', 'PROGRES', 'SCHEDULE', 'PUBLISHED', 'DONE'];
                    const counts = {};
                    stageOrder.forEach(s => { counts[s] = 0; });
                    masterPlanData.value.filter(_x => _insightInRange(_x.Tanggal_Rencana)).forEach(r => {
                        const s = (r.Status || 'IDE').toUpperCase();
                        if (counts[s] !== undefined) counts[s]++;
                        else counts['IDE']++;
                    });
                    const total = masterPlanData.value.length;
                    return stageOrder.map(stage => ({ stage, count: counts[stage], pct: total ? Math.round(counts[stage] / total * 100) : 0 }));
                });

                const monthlyTrendData = computed(() => {
                    const map = {};
                    analyticsData.value.filter(_x => _insightInRange(_x.Tanggal_Publish || _x.Tanggal_Post || _x.Tanggal)).forEach(aRow => {
                        const d = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';
                        if (!d || d.length < 7) return;
                        const ym = d.substring(0, 7);
                        if (!map[ym]) map[ym] = { ym, views: 0, likes: 0, comments: 0, count: 0 };
                        map[ym].views += Number(aRow.Views || 0);
                        map[ym].likes += Number(aRow.Likes || 0);
                        map[ym].comments += Number(aRow.Comments || 0);
                        map[ym].count++;
                    });
                    const sorted = Object.values(map).sort((a, b) => a.ym.localeCompare(b.ym));
                    return sorted.slice(-12);
                });

                const productDemandStats = computed(() => {
                    const map = {};
                    unitDitanyaData.value.filter(_x => _insightInRange(_x.TANGGAL)).forEach(r => {
                        const key = `${r.BRAND || ''}||${r.SERI || ''}`;
                        if (!map[key]) map[key] = { brand: r.BRAND || '-', seri: r.SERI || '-', total: 0, available: 0, notAvailable: 0 };
                        const qty = Number(r.DITANYA || 1);
                        map[key].total += qty;
                        if ((r.AVAILABLE || '').toUpperCase().includes('TERSEDIA') && !(r.AVAILABLE || '').toUpperCase().includes('TIDAK')) map[key].available += qty;
                        else map[key].notAvailable += qty;
                    });
                    return Object.values(map).sort((a, b) => b.total - a.total).slice(0, 20);
                });

                const orderOnlineSummary = computed(() => {
                    const map = {};
                    const start = commonDateFilter.value.start;
                    const end = commonDateFilter.value.end;
                    orderanOnlineData.value.filter(_x => _insightInRange(_x.TANGGAL)).forEach(r => {
                        const d = r.TANGGAL || '';
                        if (start && d && d < start) return;
                        if (end && d && d > end) return;
                        const ec = r.ECOMMERCE || 'Lainnya';
                        if (!map[ec]) map[ec] = { platform: ec, total: 0, nominalCair: 0, hargaOnline: 0, adminPctSum: 0, adminPctCount: 0 };
                        map[ec].total++;
                        map[ec].nominalCair += Number(r['NOMINAL CAIR'] || r.NOMINAL_CAIR || 0);
                        map[ec].hargaOnline += Number(r['HARGA ONLINE'] || r.HARGA_ONLINE || 0);
                        const ho = Number(r['HARGA ONLINE'] || r.HARGA_ONLINE || 0);
                        const nc = Number(r['NOMINAL CAIR'] || r.NOMINAL_CAIR || 0);
                        if (ho > 0) { map[ec].adminPctSum += (ho - nc) / ho * 100; map[ec].adminPctCount++; }
                    });
                    return Object.values(map).sort((a, b) => b.total - a.total).map(e => ({
                        ...e, avgAdminPct: e.adminPctCount ? (e.adminPctSum / e.adminPctCount).toFixed(1) : '0.0'
                    }));
                });

                const claimGaransiStats = computed(() => {
                    const statusOrder = ['NOT STARTED', 'PENDING', 'PROCESED', 'CLAIM', 'DONE'];
                    const statusCount = {};
                    statusOrder.forEach(s => { statusCount[s] = 0; });
                    const produkMap = {};
                    const lokasiMap = {};
                    let totalDays = 0; let doneCount = 0;
                    claimGaransiData.value.filter(_x => _insightInRange(_x.TANGGAL_MASUK)).forEach(r => {
                        const s = (r.STATUS || 'PENDING').toUpperCase();
                        if (statusCount[s] !== undefined) statusCount[s]++;
                        const key = `${r.TIPE || ''}||${r.SERI || r.MODEL || ''}`;
                        if (!produkMap[key]) produkMap[key] = { label: `${r.TIPE || '-'} ${r.SERI || r.MODEL || ''}`.trim(), count: 0 };
                        produkMap[key].count++;
                        const lok = r.LOKASI_KLAIM || 'Tidak Diketahui';
                        if (!lokasiMap[lok]) lokasiMap[lok] = 0;
                        lokasiMap[lok]++;
                        if (s === 'DONE' && r.TANGGAL_MASUK && r.TANGGAL_DIAMBIL) {
                            const diff = (new Date(r.TANGGAL_DIAMBIL) - new Date(r.TANGGAL_MASUK)) / 86400000;
                            if (diff >= 0) { totalDays += diff; doneCount++; }
                        }
                    });
                    return {
                        statusCount,
                        topProduk: Object.values(produkMap).sort((a, b) => b.count - a.count).slice(0, 10),
                        topLokasi: Object.entries(lokasiMap).sort((a, b) => b[1] - a[1]).map(([l, c]) => ({ lokasi: l, count: c })),
                        avgDays: doneCount ? (totalDays / doneCount).toFixed(1) : null,
                        total: claimGaransiData.value.length,
                    };
                });
                // End Insight and Tren
@endverbatim
