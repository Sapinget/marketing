@verbatim
const editorDateRange = computed(() => {
    const pm = editorMonth.value === 1 ? 12 : editorMonth.value - 1;
    const py = editorMonth.value === 1 ? editorYear.value - 1 : editorYear.value;
    return {
        start: `${py}-${String(pm).padStart(2, '0')}-26`,
        end: `${editorYear.value}-${String(editorMonth.value).padStart(2, '0')}-25`
    };
});

const editorDashboardData = computed(() => {
    const master = masterPlanData.value;
    const analytics = analyticsData.value;
    const dist = distributionData.value;

    const start = editorDateRange.value.start;
    const end = editorDateRange.value.end;

    const distByMaster = new Map();
    dist.forEach(d => {
        const key = String(d.Master_ID || '');
        if (!key) return;
        if (!distByMaster.has(key)) distByMaster.set(key, []);
        distByMaster.get(key).push(d);
    });

    const analyticsGrouped = new Map();
    analytics.forEach(a => {
        const key = String(a.Master_ID || '');
        if (!key) return;
        if (!analyticsGrouped.has(key)) analyticsGrouped.set(key, { views: 0, likes: 0, comments: 0, shares: 0 });
        const g = analyticsGrouped.get(key);
        g.views += Number(a.Views || 0);
        g.likes += Number(a.Likes || 0);
        g.comments += Number(a.Comments || 0);
        g.shares += Number(a.Shares || 0);
    });

    const filtered = master.filter(m => {
        const status = (m.Status || '').toUpperCase();
        if (!['PUBLISHED', 'SCHEDULE', 'DONE'].includes(status)) return false;
        const distRows = distByMaster.get(String(m.ID || '')) || [];
        const rawDate = distRows[0]?.Tanggal_Publish || m.Tanggal_Rencana || '';
        if (start && rawDate && rawDate < start) return false;
        if (end && rawDate && rawDate > end) return false;
        return true;
    });

    const videoList = filtered.map(m => {
        const distRows = distByMaster.get(String(m.ID || '')) || [];
        const ag = analyticsGrouped.get(String(m.ID || '')) || { views: 0, likes: 0, comments: 0, shares: 0 };
        return {
            ID: m.ID,
            Judul: m.Judul,
            Editor: m.Editor || '-',
            Platforms: m.Platforms || '',
            DisplayDate: distRows[0]?.Tanggal_Publish || m.Tanggal_Rencana || '',
            Tanggal_Rencana: m.Tanggal_Rencana || '',
            totalViews: ag.views,
            Likes: ag.likes,
            Comments: ag.comments,
            Shares: ag.shares,
            Views: ag.views
        };
    }).sort((a, b) => (b.DisplayDate || '').localeCompare(a.DisplayDate || ''));

    const editorMap = new Map();
    videoList.forEach(v => {
        const name = v.Editor || '-';
        if (!editorMap.has(name)) editorMap.set(name, { name, count: 0, views: 0, scores: [] });
        const e = editorMap.get(name);
        e.count++;
        e.views += v.totalViews;
        e.scores.push(calculateScore(v));
    });

    const leaderboard = [...editorMap.values()]
        .map(e => ({ ...e, avgScore: e.scores.length ? parseFloat((e.scores.reduce((s, x) => s + x, 0) / e.scores.length).toFixed(1)) : 0 }))
        .sort((a, b) => b.views - a.views);

    return {
        videoList,
        leaderboard,
        totalVideos: videoList.length,
        totalViews: videoList.reduce((s, v) => s + v.totalViews, 0)
    };
});

const editorTotalPages = computed(() => Math.max(1, Math.ceil(editorDashboardData.value.videoList.length / PAGE_SIZE)));

const pagedEditorRows = computed(() => editorDashboardData.value.videoList.slice((editorPage.value - 1) * PAGE_SIZE, editorPage.value * PAGE_SIZE));

                Object.assign(menuExports, {
                    editorDateRange,
                    editorDashboardData,
                    editorTotalPages,
                    pagedEditorRows,
                });
@endverbatim
