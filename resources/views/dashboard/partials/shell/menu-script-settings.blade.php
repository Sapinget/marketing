@verbatim
                const getSettingTabCount = (key, source = settingsDraft.value) => {
                    const value = source?.[key];
                    if (Array.isArray(value)) return value.length;
                    if (isSettingObjectValue(value)) return Object.keys(value).length;
                    return 0;
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

                Object.assign(menuExports, {
                    getSettingTabCount,
                    activeSettingObjectSections,
                    getSettingFilledCount,
                    getSettingEmptyCount,
                    isSettingTabDirty,
                    settingsFilterOptions,
                    filteredSettingsMenuGroups,
                    filteredSettingsTabCount,
                    settingsDirtyTabCount,
                    filteredActiveSettingEntries,
                    resetSettingsDraft,
                    setActiveSettingTab,
                    closeSettingsDetailModal,
                    updateSettingOption,
                    addSettingOption,
                    sortSettingOptions,
                    clearEmptySettingOptions,
                    toggleSettingsBulkAdd,
                    applySettingsBulkAdd,
                    askSettingAction,
                });
@endverbatim
