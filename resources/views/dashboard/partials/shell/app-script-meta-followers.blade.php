@verbatim
                const metaFollowersData = ref([]);
                const metaFollowersLoaded = ref(false);

                const loadMetaFollowers = () => new Promise(resolve => {
                    ensureRunApi()
                        .withSuccessHandler(data => { metaFollowersData.value = Array.isArray(data) ? data : []; metaFollowersLoaded.value = true; resolve(); })
                        .withFailureHandler(() => { metaFollowersLoaded.value = true; resolve(); })
                        .getMetaFollowers();
                });

                const metaFollowersSummary = computed(() => {
                    const data = metaFollowersData.value;
                    if (!data.length) return { total: 0, latestDate: '-', latestCount: 0, firstDate: '-', firstCount: 0 };
                    const latest = data[0];
                    const oldest = data[data.length - 1];
                    return {
                        total: data.length,
                        latestDate: latest.follow_date || '-',
                        latestCount: Number(latest.count) || 0,
                        firstDate: oldest?.follow_date || '-',
                        firstCount: Number(oldest?.count) || 0,
                    };
                });
                const metaFollowersTrend = computed(() => {
                    const rows = [...metaFollowersData.value].reverse();
                    return {
                        categories: rows.map(row => row.follow_date || '-'),
                        series: rows.map(row => Number(row.count) || 0),
                    };
                });
                const renderMetaFollowersChart = () => {
                    if (typeof ApexCharts === 'undefined') return;
                    nextTick(() => {
                        const el = document.querySelector('#meta-followers-trend');
                        if (!el) return;
                        if (window.__metaFollowersChart) {
                            try { window.__metaFollowersChart.destroy(); } catch (e) { }
                        }
                        window.__metaFollowersChart = new ApexCharts(el, {
                            chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', animations: { enabled: false } },
                            series: [{ name: 'Followers', data: metaFollowersTrend.value.series }],
                            xaxis: { categories: metaFollowersTrend.value.categories, labels: { rotate: -45, style: { fontSize: '10px' } }, tickAmount: 8 },
                            yaxis: { labels: { formatter: v => formatNumber(Math.round(v)), style: { fontSize: '10px' } } },
                            stroke: { curve: 'smooth', width: 3 },
                            colors: ['#5066EB'],
                            markers: { size: 4, strokeWidth: 0 },
                            dataLabels: { enabled: false },
                            grid: { borderColor: '#f1f5f9' },
                            tooltip: { y: { formatter: v => formatNumber(v) } },
                            noData: { text: 'Belum ada data' },
                        });
                        window.__metaFollowersChart.render();
                    });
                };
                watch([() => activeTab.value, metaFollowersData], () => {
                    if (activeTab.value === 'meta_followers') renderMetaFollowersChart();
                }, { flush: 'post' });

                const handleMetaFollowersFileInput = (event) => {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;
                    if (typeof Papa === 'undefined') { showNotification('Library CSV belum termuat, refresh halaman.'); return; }
                    metaUploading.value = true;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        try {
                            let text = '';
                            const raw = e.target?.result;
                            if (raw instanceof ArrayBuffer) {
                                const decoder = new TextDecoder('utf-16le');
                                text = decoder.decode(raw).replace(/^\ufeff/, '');
                            } else {
                                text = String(raw || '');
                            }
                            const lines = text.split(/\r?\n/).filter(line => line.trim());
                            const headerIdx = lines.findIndex(line => {
                                const h = line.replace(/^["']|["']$/g, '').split(',').map(s => s.trim().replace(/^["']|["']$/g, ''));
                                return h.length >= 2 && h[0].toLowerCase() === 'date' && h[1].toLowerCase() === 'primary';
                            });
                            if (headerIdx < 0) {
                                metaUploading.value = false;
                                showNotification('Tidak dapat menemukan header Date,Primary di CSV.');
                                return;
                            }
                            const csvText = lines.slice(headerIdx).join('\n');
                            Papa.parse(csvText, {
                                header: true, skipEmptyLines: true,
                                complete: (res) => {
                                    try {
                                        const rawRows = (res.data || []).filter(row => {
                                            const dateStr = String(row.Date || '').trim();
                                            return /^\d{4}-\d{2}-\d{2}/.test(dateStr);
                                        }).map(row => ({
                                            date: String(row.Date || '').trim().slice(0, 10),
                                            count: parseInt(String(row.Primary || '0').replace(/[^0-9]/g, ''), 10) || 0,
                                        }));
                                        if (!rawRows.length) {
                                            metaUploading.value = false;
                                            showNotification('Tidak ada baris valid. Pastikan CSV memiliki kolom Date dan Primary.');
                                            return;
                                        }
                                        const executeImport = (overwrite = false) => {
                                            ensureRunApi().withSuccessHandler(result => {
                                                metaUploading.value = false;
                                                showNotification(`Import sukses: ${result.inserted || 0} baru, ${result.updated || 0} update`);
                                                metaFollowersLoaded.value = false;
                                                loadMetaFollowers();
                                            }).withFailureHandler(err => { metaUploading.value = false; notifyError('Import gagal', err, 'CSV gagal diimport.'); }).importMetaFollowers(rawRows, { overwrite });
                                        };
                                        executeImport(false);
                                    } catch (ex) { metaUploading.value = false; notifyError('Parse gagal', ex, 'CSV gagal diparse.'); }
                                },
                                error: (err) => { metaUploading.value = false; notifyError('Parse gagal', err, 'CSV gagal dibaca.'); },
                            });
                        } catch (ex) { metaUploading.value = false; notifyError('Parse gagal', ex, 'CSV gagal diproses.'); }
                    };
                    reader.readAsArrayBuffer(file);
                    event.target.value = '';
                };

                const deleteAllMetaFollowers = () => {
                    showConfirm(
                        'Hapus Semua Data Followers',
                        'Semua data followers IG akan dihapus permanen. Lanjutkan?',
                        () => {
                            metaUploading.value = true;
                            ensureRunApi().withSuccessHandler(() => {
                                metaUploading.value = false;
                                metaFollowersData.value = [];
                                showNotification('Semua data followers berhasil dihapus.');
                            }).withFailureHandler(err => {
                                metaUploading.value = false;
                                notifyError('Hapus gagal', err, 'Data followers gagal dihapus.');
                            }).deleteMetaFollowers();
                        },
                        'danger'
                    );
                };
@endverbatim
