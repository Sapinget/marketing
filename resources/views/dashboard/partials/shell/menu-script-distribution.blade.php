@verbatim
                const distributionTotalPages = computed(() => Math.max(1, Math.ceil(filteredDistributionData.value.length / PAGE_SIZE)));

                const pagedDistributionData = computed(() => filteredDistributionData.value.slice((distributionPage.value - 1) * PAGE_SIZE, distributionPage.value * PAGE_SIZE));

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

                const distributionSummary = computed(() => {
                    const d = filteredDistributionData.value || [];
                    const linked = d.filter(r => String(r.Link || '').trim()).length;
                    const plat = _sCnt(d, r => r.Platform);
                    const topPlatform = Object.entries(plat).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Distribusi', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-share-from-square', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua distribusi' },
                        { label: 'Ada Link', value: formatNumber(linked), icon: 'fa-link', color: 'text-success', subColor: 'text-success', sub: 'Terpublikasi' },
                        { label: 'Belum Link', value: formatNumber(d.length - linked), icon: 'fa-link-slash', color: 'text-danger', subColor: 'text-danger', sub: 'Belum ada link' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-tower-broadcast', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Tersebar' },
                        { label: 'Top Platform', value: topPlatform ? String(topPlatform[0]).toUpperCase() : '-', icon: 'fa-share-nodes', color: 'text-amber', sub: formatNumber(topPlatform ? topPlatform[1] : 0) + ' konten', subColor: 'text-amber' },
                    ]) };
                });

                Object.assign(menuExports, {
                    distributionTotalPages,
                    pagedDistributionData,
                    openDistModal,
                    distributionSummary,
                });
@endverbatim
