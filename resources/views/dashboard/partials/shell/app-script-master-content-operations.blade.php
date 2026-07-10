@verbatim
                const openCreateModal = () => {
                    modalType.value = "create";
                    masterForm.value = {
                        ID: null,
                        Judul: "",
                        Format_Konten: "",
                        Colab: [],
                        Editor: "",
                        Talent: [],
                        Tanggal_Rencana: todayStr(),
                        Status: statusOptions.value[0] || '',
                        Platforms: [],
                        Skrip: "Tidak",
                        Caption: "Tidak",
                        Distribution_Meta: {},
                        Link_Drive: ""
                    };
                    modalOpen.value = true;
                };

                const openEditModal = (item) => {
                    modalType.value = "edit";

                    const colabRaw = item.Colab || item.Collab || item.PIC || "";
                    const colab = Array.isArray(colabRaw)
                        ? colabRaw
                        : String(colabRaw).split(/[,;]/).map(s => s.trim()).filter(Boolean);
                    const talentRaw = item.Talent || "";
                    const talent = Array.isArray(talentRaw)
                        ? talentRaw.map(s => String(s).trim()).filter(Boolean)
                        : String(talentRaw).split(/[,;]/).map(s => s.trim()).filter(Boolean);

                    let platforms = [];
                    if (Array.isArray(item.Platforms)) {
                        platforms = item.Platforms.map(s => String(s).trim()).filter(Boolean);
                    } else if (item.Platforms && typeof item.Platforms === 'string') {
                        platforms = item.Platforms.split(/[;,]/).map(s => s.trim()).filter(Boolean);
                    }

                    let distMetaRaw = item.Distribution_Meta || item.Distribution_Details_JSON || {};
                    let distMeta = {};
                    try {
                        const parsed = typeof distMetaRaw === 'string' ? JSON.parse(distMetaRaw) : distMetaRaw;
                        distMeta = parsed && typeof parsed === 'object' ? JSON.parse(JSON.stringify(parsed)) : {};
                    } catch (e) {
                        distMeta = {};
                    }

                    platforms.forEach(p => {
                        if (!distMeta[p]) {
                            distMeta[p] = { link: '', type: 'Regular', date: '' };
                        }
                    });

                    masterForm.value = {
                        ID: item.ID,
                        Judul: item.Judul || "",
                        Format_Konten: item.Format_Konten || "",
                        Colab: colab,
                        Editor: item.Editor || "",
                        Talent: talent,
                        Tanggal_Rencana: item.Tanggal_Rencana || "",
                        Status: item.Status || statusOptions.value[0] || '',
                        Platforms: platforms,
                        Skrip: item.Skrip || "Tidak",
                        Caption: item.Caption || "Tidak",
                        Distribution_Meta: distMeta,
                        Link_Drive: item.Link_Drive || ""
                    };
                    modalOpen.value = true;
                };

                const normalizeMasterPlanRow = (item = {}) => {
                    const splitMultiValue = (value) => Array.isArray(value)
                        ? value.map(s => String(s).trim()).filter(Boolean)
                        : String(value || '').split(/[,;]/).map(s => s.trim()).filter(Boolean);
                    const colabRaw = item.Colab || item.Collab || item.PIC || "";
                    const colab = Array.isArray(colabRaw)
                        ? colabRaw
                        : String(colabRaw || '').split(/[,;]/).map(s => s.trim()).filter(Boolean).join(', ');
                    const talentList = splitMultiValue(item.Talent || '');

                    const platformList = Array.isArray(item.Platforms)
                        ? item.Platforms.map(s => String(s).trim()).filter(Boolean)
                        : String(item.Platforms || '').split(/[;,]/).map(s => s.trim()).filter(Boolean);

                    let distMeta = {};
                    try {
                        const raw = item.Distribution_Meta || item.Distribution_Details_JSON || {};
                        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
                        if (parsed && typeof parsed === 'object') {
                            Object.entries(parsed).forEach(([key, value]) => {
                                const normalizedKey = String(key || '').trim();
                                if (!normalizedKey || normalizedKey === 'contentType') return;
                                if (value && typeof value === 'object') {
                                    distMeta[normalizedKey] = {
                                        link: String(value.link || '').trim(),
                                        type: String(value.type || '').trim(),
                                        date: String(value.date || '').trim(),
                                    };
                                }
                            });
                        }
                    } catch (e) {
                        distMeta = {};
                    }

                    platformList.forEach((plat) => {
                        if (!distMeta[plat]) distMeta[plat] = { link: '', type: 'Regular', date: '' };
                    });

                    return {
                        ...item,
                        Colab: colab,
                        Talent: talentList.join(', '),
                        TalentList: talentList,
                        Platforms: platformList.join(', '),
                        Distribution_Meta: distMeta,
                        Link_Drive: String(item.Link_Drive || '').trim(),
                    };
                };

                const normalizeMasterPlanRows = (rows) => Array.isArray(rows) ? rows.map(normalizeMasterPlanRow) : [];

                const fetchMasterPlansFromDatabase = async () => {
                    if (!ensureRunApi().isWebProxy) {
                        return new Promise((resolve, reject) => {
                            ensureRunApi()
                                .withSuccessHandler((data) => resolve(normalizeMasterPlanRows(data)))
                                .withFailureHandler(reject)
                                .getMasterPlanData();
                        });
                    }

                    const response = await fetch(resolveAppUrl('/api/master-plans'), {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error(`Gagal memuat Master Konten (${response.status})`);
                    }
                    const payload = await response.json();
                    return normalizeMasterPlanRows(payload.data);
                };

                const loadMasterPlanData = async () => {
                    try {
                        masterPlanData.value = await fetchMasterPlansFromDatabase();
                        return;
                    } catch (error) {
                        return new Promise((resolve, reject) => {
                            ensureRunApi().withSuccessHandler((data) => {
                                masterPlanData.value = normalizeMasterPlanRows(data);
                                resolve();
                            }).withFailureHandler(reject).getMasterPlanData();
                        });
                    }
                };

                const saveMasterPlan = async () => {
                    if (submitting.value) return;
                    if (!masterForm.value.Judul || !masterForm.value.Editor) {
                        showNotification("Judul dan Editor wajib diisi");
                        return;
                    }

                    const isEdit = !!masterForm.value.ID;
                    submitting.value = true;

                    const formData = {
                        ID: masterForm.value.ID || null,
                        Judul: masterForm.value.Judul,
                        Format_Konten: masterForm.value.Format_Konten,
                        Platforms: Array.isArray(masterForm.value.Platforms) ? masterForm.value.Platforms.join(', ') : (masterForm.value.Platforms || ''),
                        Colab: Array.isArray(masterForm.value.Colab) ? masterForm.value.Colab.join(', ') : (masterForm.value.Colab || ''),
                        Editor: masterForm.value.Editor,
                        Talent: Array.isArray(masterForm.value.Talent) ? masterForm.value.Talent.join(', ') : (masterForm.value.Talent || ''),
                        Skrip: masterForm.value.Skrip || 'Tidak',
                        Caption: masterForm.value.Caption || 'Tidak',
                        Status: masterForm.value.Status || statusOptions.value[0] || '',
                        Tanggal_Rencana: masterForm.value.Tanggal_Rencana || '',
                        Distribution_Meta: JSON.stringify(masterForm.value.Distribution_Meta || {}),
                        Link_Drive: masterForm.value.Link_Drive || '',
                        _actor: currentUser.value?.username || '',
                    };

                    ensureRunApi()
                        .withSuccessHandler((response) => {
                            submitting.value = false;
                            const saved = response?.data || response;
                            if (saved && saved.ID) {
                                const normalizedSaved = normalizeMasterPlanRow(saved);
                                if (isEdit) {
                                    const idx = masterPlanData.value.findIndex(r => r.ID === saved.ID);
                                    if (idx !== -1) masterPlanData.value[idx] = { ...masterPlanData.value[idx], ...normalizedSaved };
                                } else {
                                    masterPlanData.value = [normalizedSaved, ...masterPlanData.value];
                                }
                            } else {
                                loadMasterPlanData();
                            }
                            modalOpen.value = false;
                            showNotification(isEdit ? "Berhasil memperbarui rencana" : "Berhasil menambah rencana");
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveMasterPlan(formData);
                };

                const exportExcel = () => {
                    if (activeTab.value === 'unit_ditanya') { exportUnitDitanyaToExcel(); return; }
                    if (activeTab.value === 'claim_garansi_asuransi') { exportClaimGaransiToExcel(); return; }
                    if (activeTab.value === 'keep_barang') { exportKeepBarangToExcel(); return; }
                    if (activeTab.value === 'bonus_report') { exportBonusToExcel(); return; }
                    if (activeTab.value === 'sell_out') { exportSellOutToExcel(); return; }
                    if (activeTab.value === 'budgeting') { exportBudgetToExcel(); return; }
                    showNotification("Menyiapkan file Excel...");
                    const dataMap = {
                        'master': masterPlanData.value,
                        'ideation': masterPlanData.value,
                        'distribution': distributionData.value,
                        'analytics': analyticsData.value,
                        'unboxing': unboxingData.value,
                        'orderan_online': orderanOnlineData.value,
                        'top_content_platform': topContentCombined.value.map(r => ({ Platform: r.platform, Judul: r.title, Editor: r.editor, Views: r.views, Tanggal: formatShortDate(r.date) })),
                        'low_content_platform': lowContentCombined.value.map(r => ({ Platform: r.platform, Judul: r.title, Editor: r.editor, Views: r.views, Tanggal: formatShortDate(r.date) }))
                    };
                    const currentData = dataMap[activeTab.value] || [];
                    if (currentData.length === 0) {
                        showNotification("Tidak ada data untuk diekspor");
                        return;
                    }

                    ensureRunApi().withSuccessHandler((url) => {
                        if (url) {
                            const win = window.open(url, '_blank');
                            if (!win || win.closed) {
                                showNotification("Izinkan popup untuk mengunduh file Excel", 'warning');
                            } else {
                                showNotification("Ekspor berhasil dimulai");
                            }
                        }
                    }).exportToExcel(activeTab.value, currentData);
                };

                const formatShortDate = window.MarketingDashboardRuntimeHelpers?.formatShortDate || ((d) => {
                    if (!d) return '';
                    const date = new Date(d);
                    if (isNaN(date.getTime())) return String(d);
                    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
                });
                const getStatusColor = window.MarketingDashboardRuntimeHelpers?.getStatusColor || ((s) => {
                    const map = { DRAFT: '#94a3b8', PUBLISHED: '#22c55e', SCHEDULED: '#3b82f6', CANCEL: '#ef4444', REVIEW: '#f59e0b' };
                    return map[String(s || '').toUpperCase()] || '#94a3b8';
                });
@endverbatim
