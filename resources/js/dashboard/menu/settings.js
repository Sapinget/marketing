export function createSettingsHelpers(deps) {
    const {
        ref, computed, nextTick,
        settings, settingsLoaded, settingsDraft, settingsDirty,
        activeSettingTab, savingSettings, settingsSearchQuery,
        settingsFilterMode, activeSettingValueSearch, settingsDetailModalOpen,
        showSettingsBulkAdd, settingsBulkAddText,
        formatNumber, showNotification, showConfirm, notifyError,
        ensureRunApi, isMobileViewport, currentUser, resolveAppUrl, jsonApi,
    } = deps;
    let _settingsLoadPromise = null;

    const settingsMenuGroups = computed(() => {
        const draft = settingsDraft.value || {};
        const exists = (k) => Object.prototype.hasOwnProperty.call(draft, k);
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
    const applySettingsFromJson = (data) => {
        settings.value = ensureSettingDefaults(data || {});
        settingsDraft.value = JSON.parse(JSON.stringify(settings.value));
        settingsLoaded.value = true;
        if (!activeSettingTab.value || !Object.prototype.hasOwnProperty.call(settingsDraft.value, activeSettingTab.value)) {
            activeSettingTab.value = getPreferredSettingTab(settingsDraft.value);
        }
        syncSettingsDirtyState();
    };
    const loadSettings = () => {
        if (_settingsLoadPromise) {
            return _settingsLoadPromise;
        }

        const runner = ensureRunApi();
        if (runner.isWebProxy && !currentUser.value) {
            applySettingsFromJson({});
            return Promise.resolve();
        }

        const loadViaRunner = () => new Promise((resolve) => {
            runner
                .withSuccessHandler((result) => {
                    applySettingsFromJson(result || {});
                    resolve();
                })
                .withFailureHandler(() => {
                    applySettingsFromJson({});
                    resolve();
                })
                .getSettings();
        });

        if (!runner.isWebProxy) {
            _settingsLoadPromise = loadViaRunner().finally(() => {
                _settingsLoadPromise = null;
            });

            return _settingsLoadPromise;
        }

        _settingsLoadPromise = fetch(resolveAppUrl('/api/settings'), { headers: { 'Accept': 'application/json' } })
            .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
            .then((payload) => {
                applySettingsFromJson(payload.data || {});
            })
            .catch(() => loadViaRunner())
            .finally(() => {
                _settingsLoadPromise = null;
            });

        return _settingsLoadPromise;
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

    return {
        settingsMenuGroups,
        getSettingTabLabel,
        isSettingObjectValue,
        isSettingTabObject,
        getSettingTabCount,
        getPreferredSettingTab,
        normalizeSettingOption,
        formatSettingSummaryValue,
        getSettingValues,
        summarizeSettingObjectValue,
        buildSettingObjectSections,
        activeSettingObjectSections,
        getSettingFilledCount,
        getSettingEmptyCount,
        getSettingDiffCount,
        isSettingTabDirty,
        settingsFilterOptions,
        filteredSettingsMenuGroups,
        filteredSettingsTabCount,
        settingsDirtyTabCount,
        settingsDirtyValueCount,
        ensureSettingDefaults,
        filteredActiveSettingEntries,
        markSettingsDirty,
        syncSettingsDirtyState,
        resetSettingsDraft,
        setActiveSettingTab,
        closeSettingsDetailModal,
        replaceSettingValues,
        focusSettingOptionInput,
        updateSettingOption,
        addSettingOption,
        sortSettingOptions,
        clearEmptySettingOptions,
        toggleSettingsBulkAdd,
        applySettingsBulkAdd,
        askSettingAction,
        applySettingsFromJson,
        loadSettings,
        saveSettingsBackend,
    };
}
