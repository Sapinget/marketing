@verbatim
                const storyTotalPages = computed(() => Math.max(1, Math.ceil(filteredStories.value.length / PAGE_SIZE)));

                const pagedStories = computed(() => filteredStories.value.slice((storyPage.value - 1) * PAGE_SIZE, storyPage.value * PAGE_SIZE));

                const storySummary = computed(() => {
                    const d = storyData.value || [];
                    const genap = d.filter(r => r.is_genap === true || r.is_genap === 1 || String(r.is_genap) === '1').length;
                    const st = _sCnt(d, r => r.Status);
                    const topStatus = Object.entries(st).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Story', value: formatNumber(d.length), unit: 'Jadwal', icon: 'fa-clapperboard', color: 'text-danger', unitColor: 'text-danger', subColor: 'text-danger', sub: 'Semua story' },
                        { label: 'Ganjil', value: formatNumber(d.length - genap), icon: 'fa-circle-half-stroke', color: 'text-amber', subColor: 'text-amber', sub: 'Minggu ganjil' },
                        { label: 'Genap', value: formatNumber(genap), icon: 'fa-circle', color: 'text-success', subColor: 'text-success', sub: 'Minggu genap' },
                        { label: 'Status', value: formatNumber(Object.keys(st).length), unit: 'Tipe', icon: 'fa-list-check', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi status' },
                        { label: 'Top Status', value: topStatus ? String(topStatus[0]).toUpperCase() : '-', icon: 'fa-tag', color: 'text-amber', sub: formatNumber(topStatus ? topStatus[1] : 0) + ' item', subColor: 'text-amber' },
                    ]) };
                });

                Object.assign(menuExports, {
                    storyTotalPages,
                    pagedStories,
                    storySummary,
                });
@endverbatim
