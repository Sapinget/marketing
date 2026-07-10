@verbatim
                // Bonus Report
                const _bonusDefault = (() => { const d = getDefaultDateRange(); const [y, m] = d.end.split('-').map(Number); return { month: m, year: y }; })();
                const bonusMonth = ref(_bonusDefault.month);
                const bonusYear = ref(_bonusDefault.year);
                const bonusFilter = computed(() => {
                    const pm = bonusMonth.value === 1 ? 12 : bonusMonth.value - 1;
                    const py = bonusMonth.value === 1 ? bonusYear.value - 1 : bonusYear.value;
                    return {
                        start: `${py}-${String(pm).padStart(2, '0')}-26`,
                        end: `${bonusYear.value}-${String(bonusMonth.value).padStart(2, '0')}-25`
                    };
                });
                const showBonusSettings = ref(false);
                const bonusPage = ref(1);
                const talentPage = ref(1);
                const _bonusConfigDefault = {
                    reelsNonColab: [
                        { min: 10000, amount: 10000 },
                        { min: 50000, amount: 25000 },
                        { min: 100000, amount: 50000 },
                        { min: 200000, amount: 75000 },
                        { min: 500000, amount: 100000 }
                    ],
                    reelsColab: [
                        { min: 50000, amount: 25000 },
                        { min: 100000, amount: 50000 },
                        { min: 200000, amount: 75000 },
                        { min: 500000, amount: 100000 }
                    ],
                    engagement: {
                        instagram: { likeUnit: 5000, likeBonus: 100000 },
                        tiktok: { likeUnit: 10000, likeBonus: 100000 },
                        youtube: { likeUnit: 5000, likeBonus: 100000 },
                        general: { commentUnit: 100, commentBonus: 100000 }
                    }
                };
                const _mergeBonusConfig = (cfg) => {
                    if (!cfg || typeof cfg !== 'object') return _bonusConfigDefault;
                    return {
                        reelsNonColab: Array.isArray(cfg.reelsNonColab) && cfg.reelsNonColab.length ? cfg.reelsNonColab : _bonusConfigDefault.reelsNonColab,
                        reelsColab: Array.isArray(cfg.reelsColab) && cfg.reelsColab.length ? cfg.reelsColab : _bonusConfigDefault.reelsColab,
                        engagement: {
                            instagram: Object.assign({}, _bonusConfigDefault.engagement.instagram, cfg.engagement?.instagram),
                            tiktok: Object.assign({}, _bonusConfigDefault.engagement.tiktok, cfg.engagement?.tiktok),
                            youtube: Object.assign({}, _bonusConfigDefault.engagement.youtube, cfg.engagement?.youtube),
                            general: Object.assign({}, _bonusConfigDefault.engagement.general, cfg.engagement?.general)
                        }
                    };
                };
                // Clear corrupted localStorage (array format from Vue Proxy serialization bug)
                try { const _bc = JSON.parse(localStorage.getItem('ppp_bonusConfig')); if (Array.isArray(_bc)) localStorage.removeItem('ppp_bonusConfig'); } catch (e) { localStorage.removeItem('ppp_bonusConfig'); }
                try { const _bgc = JSON.parse(localStorage.getItem('ppp_budgetConfig')); if (Array.isArray(_bgc)) localStorage.removeItem('ppp_budgetConfig'); } catch (e) { localStorage.removeItem('ppp_budgetConfig'); }
                const bonusConfig = ref(_mergeBonusConfig((() => { try { return JSON.parse(localStorage.getItem('ppp_bonusConfig')); } catch (e) { return null; } })()));

                // Editor Performance
                const editorPage = ref(1);

                // Harga & Kompetitor
                const hargaKompetitorData = ref([]);
                const hargaKompetitorSearch = ref('');
                const hargaKompetitorDateFilter = ref({ start: '', end: '' });
                const hargaKompetitorPage = ref(1);
                const hargaKompetitorModalOpen = ref(false);
                const hargaKompetitorModalType = ref('create');
                const hargaKompetitorForm = ref({ ID: null, Nama_Produk: '', Tanggal_Cek: '', Harga_Distributor_1: 0, Harga_Distributor_2: 0, Harga_Kompetitor: 0, Harga_Rencana_Jual: 0, Margin_Profit: 0, Selisih: 0 });

                // Ads Log
                const adsData = ref([]);
                const adsSearch = ref('');
                const adsDateFilter = ref({ start: '', end: '' });
                const adsPage = ref(1);
                const adsModalOpen = ref(false);
                const adsModalType = ref('create');
                const adsForm = ref({ ID: null, Nama: '', ID_Ads: '', Jangkauan: 0, Suka: 0, Komentar: 0, Share: 0, Rata_Komentar: 0, Tanggal: '', Biaya: 0, Sisa_Saldo: 0, Kategori: '' });
                const adsPlatformOptions = ['Meta', 'Google', 'Mekari', 'Others'];
                const adsKategoriOptions = computed(() => resolveNamaStockSettingOptions(
                    ['Ads_Kategori', 'Kategori_Ads'],
                    ['Branding', 'Promo', 'Product Launch', 'Event', 'Remarketing', 'Konten', 'Others']
                ));

                // Laporan Event (LPJK)
                const lpjkData = ref([]);
                const lpjkSearch = ref('');
                const lpjkPage = ref(1);
                const lpjkModalOpen = ref(false);
                const lpjkModalType = ref('create');
                const lpjkForm = ref({ ID: null, Nama_Event: '', Tanggal: '', Budget_Rencana: 0, Realisasi_Biaya: 0, Selisih: 0, Status: 'DRAFT', Keterangan: '' });
                const lpjkDetailModalOpen = ref(false);
                const activeLpjkRow = ref(null);
                const lpjkDetailData = ref([]);
                const lpjkDetailItem = ref({ Master_ID: '', Kategori: 'Konsumsi', Nama_Pengeluaran: '', Satuan: 0, Jumlah: 1, Total: 0, Bukti: '' });
                const lpjkExpenseCategories = ['Konsumsi', 'Dekorasi', 'Dokumentasi', 'Transportasi', 'Venue', 'Promosi', 'Perlengkapan', 'Lainnya'];
                const lpjkStatusOptions = computed(() => settings.value.Status || []);

                // Budgeting
                const _budgetDefault = { meta: { costPerAd: 0, totalAds: 0, days: 30, balance: 0 }, google: { costPerAd: 0, totalAds: 0, days: 30, balance: 0 }, mekari: { visitor: { targetPerDay: 0, days: 30, balance: 0, topupCost: 0 }, broadcast: { costPerWeek: 0, weeks: 4, specialPrice: 0, balance: 0 } }, colabPartners: [], others: [] };
                const _mergeBudgetConfig = (cfg) => {
                    if (!cfg || typeof cfg !== 'object') return _budgetDefault;
                    return {
                        meta: Object.assign({}, _budgetDefault.meta, cfg.meta || {}),
                        google: Object.assign({}, _budgetDefault.google, cfg.google || {}),
                        mekari: { visitor: Object.assign({}, _budgetDefault.mekari.visitor, (cfg.mekari || {}).visitor || {}), broadcast: Object.assign({}, _budgetDefault.mekari.broadcast, (cfg.mekari || {}).broadcast || {}) },
                        colabPartners: Array.isArray(cfg.colabPartners) ? cfg.colabPartners : [],
                        others: Array.isArray(cfg.others) ? cfg.others : []
                    };
                };
                const budgetConfig = ref(_mergeBudgetConfig((() => { try { return JSON.parse(localStorage.getItem('ppp_budgetConfig')); } catch (e) { return null; } })()));
                const budgetDateFilter = ref((() => {
                    const now = new Date();
                    const y = now.getFullYear(), m = now.getMonth();
                    const pad = n => String(n).padStart(2, '0');
                    const lastDay = new Date(y, m + 1, 0).getDate();
                    return { start: `${y}-${pad(m + 1)}-01`, end: `${y}-${pad(m + 1)}-${pad(lastDay)}` };
                })());
                const showBudgetSettings = ref(false);
                const showColabListModal = ref(false);

                // Program Promo
                const promoData = ref([]);
                const promoSearch = ref('');
                const promoPage = ref(1);
                const promoModalOpen = ref(false);
                const promoModalType = ref('create');
                const promoForm = ref({ ID: null, Kategori: '', Program: '', Warna: '', Harga: 0, Periode: 'Selama persediaan masih ada', Rules: '', Benefit: '' });
                const promoPeriodePreset = ref('');
                const promoTempDate = ref({ start: '', end: '' });

                // Sell Out
                const sellOutData = ref([]);
                const sellOutSearch = ref('');
                const sellOutVendorFilter = ref('');
                const sellOutMonth = ref(new Date().toISOString().substring(0, 7));
                const sellOutPage = ref(1);
                const sellOutModalOpen = ref(false);
                const sellOutModalType = ref('create');
                const sellOutForm = ref({ ID: null, Vendor: '', Kategori: '', Brand: '', Seri: '', RAM: '', Internal: '', Size: '', Kondisi: '', Nama_Produk: '', Target_Unit: 0, Bonus_Nominal: 0, Realisasi_Unit: 0, Periode_Start: '', Periode_End: '', Catatan: '' });
@endverbatim
