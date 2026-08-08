@verbatim
                // ── Image Repository ─────────────────────────────────────────────────────
                const imgRepoPath        = ref('');
                const imgRepoItems       = ref([]);
                const imgRepoLoading     = ref(false);
                const imgRepoError       = ref(null);
                const imgRepoSelected    = ref(null);
                const imgRepoRenameItem  = ref(null);
                const imgRepoRenameName  = ref('');
                const imgRepoMkdirOpen   = ref(false);
                const imgRepoMkdirName   = ref('');
                const imgRepoUploadOpen  = ref(false);
                const imgRepoUploading   = ref(false);
                const imgRepoDeleteTarget = ref(null);
                const imgRepoBusy        = ref(false);
                const imgRepoViewMode    = ref('grid');
                const imgRepoNewMenuOpen = ref(false);
                const imgRepoContextMenu = ref(null);

                const imgRepoBreadcrumbs = computed(() => {
                    const parts = imgRepoPath.value === '' ? [] : imgRepoPath.value.split('/');
                    const crumbs = [{ label: 'Semua', path: '' }];
                    let acc = '';
                    for (const p of parts) {
                        acc = acc === '' ? p : acc + '/' + p;
                        crumbs.push({ label: p, path: acc });
                    }
                    return crumbs;
                });

                const imgRepoDirs  = computed(() => imgRepoItems.value.filter(i => i.type === 'dir'));
                const imgRepoFiles = computed(() => imgRepoItems.value.filter(i => i.type === 'file'));

                const imgRepoThumbUrl = (path) => '/api/img-repo/serve?path=' + encodeURIComponent(path);

                async function imgRepoBrowse(path = '') {
                    imgRepoLoading.value = true;
                    imgRepoError.value   = null;
                    imgRepoSelected.value = null;
                    imgRepoRenameItem.value = null;
                    try {
                        const res = await fetch('/api/img-repo/browse?path=' + encodeURIComponent(path));
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const json = await res.json();
                        imgRepoPath.value  = json.path;
                        imgRepoItems.value = json.items || [];
                    } catch (e) {
                        imgRepoError.value = e.message || 'Gagal memuat direktori';
                    } finally {
                        imgRepoLoading.value = false;
                    }
                }

                async function imgRepoMkdir() {
                    const name = imgRepoMkdirName.value.trim();
                    if (!name) return;
                    imgRepoBusy.value = true;
                    try {
                        const fd = new FormData();
                        fd.append('path', imgRepoPath.value);
                        fd.append('name', name);
                        const res = await fetch('/api/img-repo/mkdir', { method: 'POST', body: fd });
                        if (!res.ok) { const j = await res.json(); throw new Error(j.message || 'Error'); }
                        imgRepoMkdirOpen.value = false;
                        imgRepoMkdirName.value = '';
                        await imgRepoBrowse(imgRepoPath.value);
                    } catch (e) {
                        alert('Gagal membuat folder: ' + e.message);
                    } finally {
                        imgRepoBusy.value = false;
                    }
                }

                async function imgRepoRenameSubmit() {
                    const item = imgRepoRenameItem.value;
                    const name = imgRepoRenameName.value.trim();
                    if (!item || !name) return;
                    imgRepoBusy.value = true;
                    try {
                        const fd = new FormData();
                        fd.append('path', item.path);
                        fd.append('name', name);
                        const res = await fetch('/api/img-repo/rename', { method: 'POST', body: fd });
                        if (!res.ok) { const j = await res.json(); throw new Error(j.message || 'Error'); }
                        imgRepoRenameItem.value = null;
                        imgRepoRenameName.value = '';
                        await imgRepoBrowse(imgRepoPath.value);
                    } catch (e) {
                        alert('Gagal rename: ' + e.message);
                    } finally {
                        imgRepoBusy.value = false;
                    }
                }

                async function imgRepoDelete(item) {
                    if (!confirm('Hapus "' + item.name + '"?\nTidak bisa dibatalkan.')) return;
                    imgRepoBusy.value = true;
                    try {
                        const res = await fetch('/api/img-repo/delete', {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ path: item.path }),
                        });
                        if (!res.ok) { const j = await res.json(); throw new Error(j.message || 'Error'); }
                        if (imgRepoSelected.value?.path === item.path) imgRepoSelected.value = null;
                        await imgRepoBrowse(imgRepoPath.value);
                    } catch (e) {
                        alert('Gagal hapus: ' + e.message);
                    } finally {
                        imgRepoBusy.value = false;
                    }
                }

                async function imgRepoUploadFiles(event) {
                    const files = event.target.files;
                    if (!files || !files.length) return;
                    imgRepoUploading.value = true;
                    try {
                        const fd = new FormData();
                        fd.append('path', imgRepoPath.value);
                        for (const f of files) fd.append('files[]', f);
                        const res = await fetch('/api/img-repo/upload', { method: 'POST', body: fd });
                        if (!res.ok) { const j = await res.json(); throw new Error(j.message || 'Error'); }
                        event.target.value = '';
                        await imgRepoBrowse(imgRepoPath.value);
                    } catch (e) {
                        alert('Gagal upload: ' + e.message);
                    } finally {
                        imgRepoUploading.value = false;
                    }
                }

                function imgRepoStartRename(item) {
                    imgRepoRenameItem.value = item;
                    imgRepoRenameName.value = item.name;
                    imgRepoSelected.value   = null;
                }

                function imgRepoCancelRename() {
                    imgRepoRenameItem.value = null;
                    imgRepoRenameName.value = '';
                }

                function imgRepoNavigate(item) {
                    if (item.type === 'dir') {
                        imgRepoBrowse(item.path);
                    } else {
                        imgRepoSelected.value = imgRepoSelected.value?.path === item.path ? null : item;
                    }
                }

                function imgRepoSelectItem(item) {
                    imgRepoContextMenu.value = null;
                    imgRepoSelected.value = imgRepoSelected.value?.path === item.path ? null : item;
                }

                function imgRepoOpenContext(event, item) {
                    imgRepoSelected.value = item;
                    const margin = 8;
                    const menuW = 176;
                    const menuH = item.type === 'dir' ? 120 : 96;
                    const x = Math.min(event.clientX, window.innerWidth - menuW - margin);
                    const y = Math.min(event.clientY, window.innerHeight - menuH - margin);
                    imgRepoContextMenu.value = { item, x, y };
                }
                // ─────────────────────────────────────────────────────────────────────────
@endverbatim
