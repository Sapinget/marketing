@verbatim
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

                Object.assign(menuExports, {
                    talentBonusRows,
                    talentDashboardData,
                    talentTotalPages,
                    pagedTalentRows,
                });
@endverbatim
