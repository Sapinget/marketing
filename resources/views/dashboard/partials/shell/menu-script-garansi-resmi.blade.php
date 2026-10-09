@verbatim
                const garansiResmiSummary = computed(() => {
                    const d = filteredGaransiResmiRows.value || [];
                    const st = _sCnt(d, r => String(r['Status perbaikan'] || '').trim());
                    const done = st['Done'] || 0;
                    const progress = st['Progress Claim'] || 0;
                    const notStarted = st['Not Started'] || 0;
                    return { cards: limitSummaryCards([
                        { label: 'Total Klaim', value: formatNumber(d.length), unit: 'Unit', icon: 'fa-screwdriver-wrench', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua klaim resmi' },
                        { label: 'Done', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Klaim selesai' },
                        { label: 'Progress', value: formatNumber(progress), icon: 'fa-spinner', color: 'text-amber', subColor: 'text-amber', sub: 'Sedang diproses' },
                        { label: 'Not Started', value: formatNumber(notStarted), icon: 'fa-hourglass-half', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Belum dimulai' },
                    ]) };
                });

                const garansiResmiTotalPages = computed(() => Math.max(1, Math.ceil(filteredGaransiResmiRows.value.length / PAGE_SIZE)));

                const pagedGaransiResmiRows = computed(() => filteredGaransiResmiRows.value.slice((garansiResmiPage.value - 1) * PAGE_SIZE, garansiResmiPage.value * PAGE_SIZE));

                Object.assign(menuExports, {
                    garansiResmiSummary,
                    garansiResmiTotalPages,
                    pagedGaransiResmiRows,
                });
@endverbatim
