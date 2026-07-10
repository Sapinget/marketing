@verbatim
                if (!window.MarketingDashboardRuntimeHelpers || !window.MarketingDashboardRuntimeHelpers.createSettingsHelpers) {
                    window.MarketingDashboardRuntimeHelpers = {
                        ...(window.MarketingDashboardRuntimeHelpers || {}),
                        createSettingsHelpers: (deps) => {
                            const { ref: _ref, computed: _computed, nextTick: _nextTick, settings: _settings, settingsLoaded: _settingsLoaded, settingsDraft: _settingsDraft, settingsDirty: _settingsDirty, activeSettingTab: _activeSettingTab, savingSettings: _savingSettings, settingsSearchQuery: _settingsSearchQuery, settingsFilterMode: _settingsFilterMode, activeSettingValueSearch: _activeSettingValueSearch, settingsDetailModalOpen: _settingsDetailModalOpen, showSettingsBulkAdd: _showSettingsBulkAdd, settingsBulkAddText: _settingsBulkAddText, formatNumber: _formatNumber, showNotification: _showNotification, showConfirm: _showConfirm, notifyError: _notifyError, ensureRunApi: _ensureRunApi, isMobileViewport: _isMobileViewport, currentUser: _currentUser, resolveAppUrl: _resolveAppUrl, jsonApi: _jsonApi } = deps;
                            const _getSettingTabLabel = (key) => String(key || '').replace(/_/g, ' ');
                            const _isSettingObjectValue = (value) => Boolean(value) && typeof value === 'object' && !Array.isArray(value);
                            const _normalizeSettingOption = (value) => String(value ?? '').trim();
                            const _settingsMenuGroups = _computed(() => {
                                const draft = _settingsDraft.value || {};
                                const exists = (k) => Object.prototype.hasOwnProperty.call(draft, k);
                                const groups = [{ label: "Content", keys: ["Format_Konten", "Platforms", "Colab", "Editor", "Talent", "Status", "Tipe_Konten"] }, { label: "Customer Service", keys: ["Orderan_Online_Ecommerce", "Orderan_Online_Handle", "Orderan_Online_Pengiriman", "Orderan_Online_Status", "Kondisi_Produk"] }];
                                const grouped = groups.map(g => ({ label: g.label, keys: g.keys.filter(k => exists(k)) })).filter(g => g.keys.length > 0);
                                const known = new Set(groups.flatMap(g => g.keys));
                                const extras = Object.keys(draft).filter(k => !known.has(k));
                                if (extras.length > 0) grouped.push({ label: "Lainnya", keys: extras });
                                return grouped;
                            });
                            const _getSettingValues = (key, source = _settingsDraft.value) => Array.isArray(source?.[key]) ? source[key] : [];
                            const _formatSettingSummaryValue = (value) => {
                                if (typeof value === 'number' && Number.isFinite(value)) return _formatNumber(value);
                                const ns = String(value ?? '').trim();
                                if (/^-?\d+(\.\d+)?$/.test(ns)) return _formatNumber(Number(ns));
                                return String(value ?? '-');
                            };
                            const _summarizeSettingObjectValue = (value) => {
                                if (Array.isArray(value)) {
                                    if (!value.length) return '-';
                                    return value.map((item) => {
                                        if (_isSettingObjectValue(item)) return Object.entries(item).map(([ik, iv]) => `${_getSettingTabLabel(ik)}: ${_formatSettingSummaryValue(iv)}`).join(' | ');
                                        return _formatSettingSummaryValue(item);
                                    }).join('\n');
                                }
                                if (_isSettingObjectValue(value)) {
                                    const parts = Object.entries(value).map(([ik, iv]) => `${_getSettingTabLabel(ik)}: ${_formatSettingSummaryValue(iv)}`);
                                    return parts.length ? parts.join(' | ') : '-';
                                }
                                return _formatSettingSummaryValue(value);
                            };
                            const _buildSettingObjectSections = (key, source = _settingsDraft.value) => {
                                const value = source?.[key];
                                if (!_isSettingObjectValue(value)) return [];
                                return Object.entries(value).map(([sk, sv]) => {
                                    let items = [];
                                    if (Array.isArray(sv)) items = sv.map((e, i) => ({ label: `Item ${i + 1}`, value: _summarizeSettingObjectValue(e) }));
                                    else if (_isSettingObjectValue(sv)) items = Object.entries(sv).map(([ik, iv]) => ({ label: _getSettingTabLabel(ik), value: _summarizeSettingObjectValue(iv) }));
                                    else items = [{ label: _getSettingTabLabel(sk), value: _summarizeSettingObjectValue(sv) }];
                                    return { title: _getSettingTabLabel(sk), items };
                                });
                            };
                            const _getSettingDiffCount = (key) => {
                                if (_isSettingObjectValue(_settingsDraft.value?.[key])) return JSON.stringify(_settingsDraft.value?.[key] ?? null) === JSON.stringify(_settings.value?.[key] ?? null) ? 0 : 1;
                                const current = _getSettingValues(key).map(v => String(v ?? ''));
                                const saved = _getSettingValues(key, _settings.value).map(v => String(v ?? ''));
                                if (current.length !== saved.length) return Math.abs(current.length - saved.length) + current.filter((v, idx) => v !== (saved[idx] ?? '')).length;
                                return current.filter((v, idx) => v !== saved[idx]).length;
                            };
                            const _ensureSettingDefaults = (source = {}) => {
                                const n = { ...(source || {}) };
                                ['Format_Konten', 'Platforms', 'Colab', 'Editor', 'Talent', 'Status', 'Tipe_Konten'].forEach(k => { if (!Object.prototype.hasOwnProperty.call(n, k) || !Array.isArray(n[k])) n[k] = Array.isArray(n[k]) ? [...n[k]] : []; });
                                return n;
                            };
                            return {
                                settingsMenuGroups: _settingsMenuGroups,
                                getSettingTabLabel: _getSettingTabLabel,
                                isSettingObjectValue: _isSettingObjectValue,
                                normalizeSettingOption: _normalizeSettingOption,
                                getSettingValues: _getSettingValues,
                                formatSettingSummaryValue: _formatSettingSummaryValue,
                                summarizeSettingObjectValue: _summarizeSettingObjectValue,
                                buildSettingObjectSections: _buildSettingObjectSections,
                                getSettingDiffCount: _getSettingDiffCount,
                                ensureSettingDefaults: _ensureSettingDefaults,
                            };
                        },
                    };
                }

                const settingsMenuGroups = computed(() => {
                    const draft = settingsDraft.value || {};
                    const exists = (k) => Object.prototype.hasOwnProperty.call(draft, k);
                    // 'Talent' is a first-class settings key for master plan assignment.
                    const groups = [
                        { label: "Content", keys: ["Format_Konten", "Platforms", "Colab", "Editor", "Talent", "Status", "Tipe_Konten"] },
                        { label: "Customer Service", keys: ["Orderan_Online_Ecommerce", "Orderan_Online_Handle", "Orderan_Online_Pengiriman", "Orderan_Online_Status", "Kondisi_Produk"] }
                    ];
                    const grouped = groups.map(g => ({
                        label: g.label,
                        keys: g.keys.filter(k => exists(k))
                    })).filter(g => g.keys.length > 0);
                    const known = new Set(groups.flatMap(g => g.keys));
                    const extras = Object.keys(draft).filter(k => !known.has(k));
                    if (extras.length > 0) grouped.push({ label: "Lainnya", keys: extras });
                    return grouped;
                });

                const getSettingTabLabel = (key) => String(key || '').replace(/_/g, ' ');
                const isSettingObjectValue = (value) => Boolean(value) && typeof value === 'object' && !Array.isArray(value);
                const isSettingTabObject = (key, source = settingsDraft.value) => isSettingObjectValue(source?.[key]);
                const getSettingTabCount = (key, source = settingsDraft.value) => {
                    const value = source?.[key];
                    if (Array.isArray(value)) return value.length;
                    if (isSettingObjectValue(value)) return Object.keys(value).length;
                    return 0;
                };
                const getPreferredSettingTab = (source = settingsDraft.value) => {
                    const groups = settingsMenuGroups.value || [];
                    const orderedKeys = groups.flatMap((group) => group.keys);
                    const firstFilledKey = orderedKeys.find((key) => {
                        const value = source?.[key];
                        if (Array.isArray(value)) return value.some((item) => String(item ?? '').trim());
                        if (isSettingObjectValue(value)) return Object.keys(value).length > 0;
                        return false;
                    });
                    return firstFilledKey || orderedKeys[0] || Object.keys(source || {})[0] || null;
                };
                const normalizeSettingOption = (value) => String(value ?? '').trim();
                const formatSettingSummaryValue = (value) => {
                    if (typeof value === 'number' && Number.isFinite(value)) {
                        return formatNumber(value);
                    }
                    const numericString = String(value ?? '').trim();
                    if (/^-?\d+(\.\d+)?$/.test(numericString)) {
                        return formatNumber(Number(numericString));
                    }
                    return String(value ?? '-');
                };
                const getSettingValues = (key, source = settingsDraft.value) => Array.isArray(source?.[key]) ? source[key] : [];
                const summarizeSettingObjectValue = (value) => {
                    if (Array.isArray(value)) {
                        if (!value.length) return '-';
                        return value.map((item) => {
                            if (isSettingObjectValue(item)) {
                                return Object.entries(item).map(([itemKey, itemValue]) => `${getSettingTabLabel(itemKey)}: ${formatSettingSummaryValue(itemValue)}`).join(' | ');
                            }
                            return formatSettingSummaryValue(item);
                        }).join('\n');
                    }
                    if (isSettingObjectValue(value)) {
                        const parts = Object.entries(value).map(([itemKey, itemValue]) => `${getSettingTabLabel(itemKey)}: ${formatSettingSummaryValue(itemValue)}`);
                        return parts.length ? parts.join(' | ') : '-';
                    }
                    return formatSettingSummaryValue(value);
                };
                const buildSettingObjectSections = (key, source = settingsDraft.value) => {
                    const value = source?.[key];
                    if (!isSettingObjectValue(value)) return [];
                    return Object.entries(value).map(([sectionKey, sectionValue]) => {
                        let items = [];
                        if (Array.isArray(sectionValue)) {
                            items = sectionValue.map((entry, index) => ({
                                label: `Item ${index + 1}`,
                                value: summarizeSettingObjectValue(entry),
                            }));
                        } else if (isSettingObjectValue(sectionValue)) {
                            items = Object.entries(sectionValue).map(([itemKey, itemValue]) => ({
                                label: getSettingTabLabel(itemKey),
                                value: summarizeSettingObjectValue(itemValue),
                            }));
                        } else {
                            items = [{ label: getSettingTabLabel(sectionKey), value: summarizeSettingObjectValue(sectionValue) }];
                        }
                        return {
                            title: getSettingTabLabel(sectionKey),
                            items,
                        };
                    });
                };
                const activeSettingObjectSections = computed(() => buildSettingObjectSections(activeSettingTab.value));
                const getSettingFilledCount = (key) => {
                    if (isSettingTabObject(key)) return buildSettingObjectSections(key).reduce((total, section) => total + section.items.filter((item) => normalizeSettingOption(item.value) && item.value !== '-').length, 0);
                    return getSettingValues(key).filter((value) => normalizeSettingOption(value)).length;
                };
                const getSettingEmptyCount = (key) => {
                    if (isSettingTabObject(key)) return 0;
                    return getSettingValues(key).filter((value) => !normalizeSettingOption(value)).length;
                };
                const getSettingDiffCount = (key) => {
                    if (isSettingTabObject(key)) {
                        return JSON.stringify(settingsDraft.value?.[key] ?? null) === JSON.stringify(settings.value?.[key] ?? null) ? 0 : 1;
                    }
                    const current = getSettingValues(key).map((value) => String(value ?? ''));
                    const saved = getSettingValues(key, settings.value).map((value) => String(value ?? ''));
                    if (current.length !== saved.length) return Math.abs(current.length - saved.length) + current.filter((value, idx) => value !== (saved[idx] ?? '')).length;
                    return current.filter((value, idx) => value !== saved[idx]).length;
                };
                const isSettingTabDirty = (key) => getSettingDiffCount(key) > 0;
                const settingsFilterOptions = Object.freeze([
                    { value: 'all', label: 'Semua' },
                    { value: 'dirty', label: 'Diubah' },
                    { value: 'empty', label: 'Kosong' },
                ]);
                const filteredSettingsMenuGroups = computed(() => {
                    const query = String(settingsSearchQuery.value || '').trim().toLowerCase();
                    const mode = settingsFilterMode.value;
                    return settingsMenuGroups.value
                        .map((group) => ({
                            ...group,
                            keys: group.keys.filter((key) => {
                                const label = getSettingTabLabel(key).toLowerCase();
                                const matchQuery = !query || label.includes(query) || String(key || '').toLowerCase().includes(query);
                                const matchMode = mode === 'dirty'
                                    ? isSettingTabDirty(key)
                                    : mode === 'empty'
                                        ? getSettingEmptyCount(key) > 0
                                        : true;
                                return matchQuery && matchMode;
                            }),
                        }))
                        .filter((group) => group.keys.length > 0);
                });
                const filteredSettingsTabCount = computed(() => filteredSettingsMenuGroups.value.reduce((total, group) => total + group.keys.length, 0));
                const settingsDirtyTabCount = computed(() => Object.keys(settingsDraft.value || {}).filter((key) => isSettingTabDirty(key)).length);
                const settingsDirtyValueCount = computed(() => Object.keys(settingsDraft.value || {}).reduce((total, key) => total + getSettingDiffCount(key), 0));
                const ensureSettingDefaults = (source = {}) => {
                    const normalized = { ...(source || {}) };
                    ['Format_Konten', 'Platforms', 'Colab', 'Editor', 'Talent', 'Status', 'Tipe_Konten'].forEach((key) => {
                        if (!Object.prototype.hasOwnProperty.call(normalized, key) || !Array.isArray(normalized[key])) {
                            normalized[key] = Array.isArray(normalized[key]) ? [...normalized[key]] : [];
                        }
                    });
                    return normalized;
                };
                const filteredActiveSettingEntries = computed(() => {
                    const key = activeSettingTab.value;
                    if (!key) return [];
                    if (isSettingTabObject(key)) return [];
                    const query = String(activeSettingValueSearch.value || '').trim().toLowerCase();
                    return getSettingValues(key).map((value, idx) => ({ value, idx })).filter((entry) => {
                        if (!query) return true;
                        return String(entry.value || '').toLowerCase().includes(query);
                    });
                });
                const markSettingsDirty = () => { settingsDirty.value = true; };
                const syncSettingsDirtyState = () => {
                    settingsDirty.value = settingsDirtyValueCount.value > 0;
                };
                const resetSettingsDraft = () => {
                    settingsDraft.value = JSON.parse(JSON.stringify(ensureSettingDefaults(settings.value)));
                    settingsDirty.value = false;
                    activeSettingValueSearch.value = '';
                    showSettingsBulkAdd.value = false;
                    settingsBulkAddText.value = '';
                };
                const setActiveSettingTab = (key) => {
                    activeSettingTab.value = key;
                    activeSettingValueSearch.value = '';
                    showSettingsBulkAdd.value = false;
                    settingsBulkAddText.value = '';
                    if (isMobileViewport.value) settingsDetailModalOpen.value = true;
                };
                const closeSettingsDetailModal = () => {
                    settingsDetailModalOpen.value = false;
                    activeSettingValueSearch.value = '';
                    showSettingsBulkAdd.value = false;
                    settingsBulkAddText.value = '';
                };
                const replaceSettingValues = (key, nextValues) => {
                    settingsDraft.value = {
                        ...settingsDraft.value,
                        [key]: [...nextValues],
                    };
                    syncSettingsDirtyState();
                };
                const focusSettingOptionInput = (key, idx = 0) => {
                    nextTick(() => {
                        const input = document.querySelector(`[data-setting-key="${key}"][data-setting-idx="${idx}"]`);
                        if (!input) return;
                        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        if (typeof input.focus === 'function') input.focus();
                        if (typeof input.select === 'function') input.select();
                    });
                };
                const updateSettingOption = (key, idx, value) => {
                    const values = [...getSettingValues(key)];
                    values[idx] = String(value ?? '');
                    replaceSettingValues(key, values);
                };
                const addSettingOption = (key, initialValue = '') => {
                    replaceSettingValues(key, [String(initialValue ?? ''), ...getSettingValues(key)]);
                    focusSettingOptionInput(key, 0);
                };
                const sortSettingOptions = (key) => {
                    const values = [...getSettingValues(key)];
                    const sorted = values.sort((a, b) => normalizeSettingOption(a).localeCompare(normalizeSettingOption(b), 'id', { sensitivity: 'base' }));
                    replaceSettingValues(key, sorted);
                };
                const clearEmptySettingOptions = (key) => {
                    const values = getSettingValues(key);
                    const cleaned = values.filter((value) => normalizeSettingOption(value));
                    if (cleaned.length === values.length) {
                        showNotification('Tidak ada opsi kosong untuk dihapus.');
                        return;
                    }
                    replaceSettingValues(key, cleaned);
                    showNotification('Opsi kosong berhasil dihapus.');
                };
                const toggleSettingsBulkAdd = () => {
                    showSettingsBulkAdd.value = !showSettingsBulkAdd.value;
                    if (!showSettingsBulkAdd.value) settingsBulkAddText.value = '';
                };
                const applySettingsBulkAdd = (key) => {
                    const incoming = String(settingsBulkAddText.value || '')
                        .split(/\r?\n/)
                        .map((value) => normalizeSettingOption(value))
                        .filter(Boolean);
                    if (!incoming.length) {
                        showNotification('Isi daftar dulu sebelum tambah banyak.');
                        return;
                    }
                    const merged = Array.from(new Set([...getSettingValues(key), ...incoming].map((value) => String(value ?? ''))));
                    replaceSettingValues(key, merged);
                    settingsBulkAddText.value = '';
                    showSettingsBulkAdd.value = false;
                    showNotification(`${incoming.length} opsi diproses.`);
                };
                const askSettingAction = (action, key, idx) => {
                    if (action === 'delete') {
                        showConfirm("Hapus Opsi?", "Opsi ini akan dihapus dari daftar.", () => {
                            const nextValues = getSettingValues(key).filter((_, itemIdx) => itemIdx !== idx);
                            replaceSettingValues(key, nextValues);
                        });
                    } else if (action === 'add') {
                        addSettingOption(key);
                    }
                };
                const applySettings = (data) => {
                    settings.value = ensureSettingDefaults(data || {});
                    settingsDraft.value = JSON.parse(JSON.stringify(settings.value));
                    settingsLoaded.value = true;
                    if (!activeSettingTab.value || !Object.prototype.hasOwnProperty.call(settingsDraft.value, activeSettingTab.value)) {
                        activeSettingTab.value = getPreferredSettingTab(settingsDraft.value);
                    }
                    syncSettingsDirtyState();
                };
                const resolveAppUrl = window.MarketingDashboardRuntimeHelpers?.resolveAppUrl || ((url) => {
                    if (!url || /^https?:\/\//i.test(url)) return url;
                    if (window.MARKETING_BACKEND_URL) {
                        return `${String(window.MARKETING_BACKEND_URL).replace(/\/+$/, '')}${url}`;
                    }
                    return url;
                });
                const csrfHeaderFallback = () => {
                    const cookie = document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='));
                    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
                };
                const jsonApi = window.MarketingDashboardRuntimeHelpers?.jsonApi || (async (url, options = {}) => {
                    const token = csrfHeaderFallback();
                    const response = await fetch(resolveAppUrl(url), {
                        ...options,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
                            ...(options.headers || {})
                        },
                    });
                    if (!response.ok) {
                        if (response.status === 401) {
                            const unauthorizedError = new Error('Sesi login berakhir. Silakan login kembali.');
                            unauthorizedError.status = 401;
                            throw unauthorizedError;
                        }
                        let payload = null;
                        try { payload = await response.json(); } catch (error) { payload = null; }
                        const errorMessages = payload && payload.errors && typeof payload.errors === 'object'
                            ? Object.values(payload.errors).flat().filter(Boolean)
                            : [];
                        const message = errorMessages[0] || (payload && payload.message) || `HTTP ${response.status}`;
                        const requestError = new Error(message);
                        requestError.status = response.status;
                        requestError.payload = payload;
                        throw requestError;
                    }
                    return response.status === 204 ? null : response.json();
                });
                const loadSettings = () => {
                    if (settingsLoadPromise) {
                        return settingsLoadPromise;
                    }

                    const runner = ensureRunApi();
                    if (runner.isWebProxy && !currentUser.value) {
                        applySettings({});
                        return Promise.resolve();
                    }

                    const loadViaRunner = () => new Promise((resolve) => {
                        runner
                            .withSuccessHandler((result) => {
                                applySettings(result || {});
                                resolve();
                            })
                            .withFailureHandler(() => {
                                applySettings({});
                                resolve();
                            })
                            .getSettings();
                    });

                    if (!runner.isWebProxy) {
                        settingsLoadPromise = loadViaRunner().finally(() => {
                            settingsLoadPromise = null;
                        });

                        return settingsLoadPromise;
                    }

                    settingsLoadPromise = fetch(resolveAppUrl('/api/settings'), { headers: { 'Accept': 'application/json' } })
                        .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
                        .then((payload) => {
                            applySettings(payload.data || {});
                        })
                        .catch(() => loadViaRunner())
                        .finally(() => {
                            settingsLoadPromise = null;
                        });

                    return settingsLoadPromise;
                };
                const saveSettingsBackend = () => {
                    savingSettings.value = true;
                    const payload = JSON.parse(JSON.stringify(settingsDraft.value));
                    const runner = ensureRunApi();
                    const applySavedSettings = () => {
                        settings.value = payload;
                        settingsDraft.value = JSON.parse(JSON.stringify(payload));
                        settingsLoaded.value = true;
                        settingsDirty.value = false;
                        savingSettings.value = false;
                        showNotification("Pengaturan berhasil disimpan");
                    };
                    const saveViaRunner = () => {
                        runner
                            .withSuccessHandler(() => {
                                applySavedSettings();
                            })
                            .withFailureHandler((err) => {
                                savingSettings.value = false;
                                notifyError('Gagal menyimpan', err, 'Pengaturan belum berhasil disimpan.');
                            })
                            .saveSettings(payload);
                    };

                    if (!runner.isWebProxy) {
                        saveViaRunner();
                        return;
                    }

                    jsonApi('/api/settings', { method: 'PUT', body: JSON.stringify({ data: payload }) })
                        .then(() => {
                            applySavedSettings();
                        })
                        .catch(() => {
                            saveViaRunner();
                        });
                };
@endverbatim
