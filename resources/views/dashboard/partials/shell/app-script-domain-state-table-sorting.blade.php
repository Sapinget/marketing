@verbatim
                const contentSortBy = ref("date");
                const contentSortDesc = ref(true);
                let tableSortObserver = null;

                const nonSortableHeaderLabels = new Set(['aksi', '#', 'no']);
                const normalizeSortableValue = (value) => {
                    const raw = String(value || '').trim();
                    if (!raw) return { kind: 'string', value: '' };

                    const isoDateMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                    if (isoDateMatch) {
                        return { kind: 'date', value: new Date(`${isoDateMatch[1]}-${isoDateMatch[2]}-${isoDateMatch[3]}T00:00:00`).getTime() };
                    }

                    const slashDateMatch = raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/);
                    if (slashDateMatch) {
                        const year = slashDateMatch[3].length === 2 ? `20${slashDateMatch[3]}` : slashDateMatch[3];
                        return { kind: 'date', value: new Date(`${year}-${slashDateMatch[2].padStart(2, '0')}-${slashDateMatch[1].padStart(2, '0')}T00:00:00`).getTime() };
                    }

                    const numeric = raw
                        .replace(/\s+/g, '')
                        .replace(/\.(?=\d{3}(\D|$))/g, '')
                        .replace(/,/g, '.')
                        .replace(/[^\d.-]/g, '');

                    if (numeric && /^-?\d+(\.\d+)?$/.test(numeric)) {
                        return { kind: 'number', value: Number(numeric) };
                    }

                    return { kind: 'string', value: raw.toLowerCase() };
                };

                const compareSortableValues = (left, right, isDescending) => {
                    const leftValue = normalizeSortableValue(left);
                    const rightValue = normalizeSortableValue(right);
                    let result = 0;

                    if (leftValue.kind === rightValue.kind && leftValue.kind !== 'string') {
                        result = leftValue.value - rightValue.value;
                    } else {
                        result = String(left || '').localeCompare(String(right || ''), 'id', { numeric: true, sensitivity: 'base' });
                    }

                    return isDescending ? result * -1 : result;
                };

                const sortTableDomRows = (headerCell) => {
                    const headerRow = headerCell?.parentElement;
                    const table = headerCell?.closest('table');
                    const tableBody = table?.querySelector('tbody');
                    if (!headerRow || !table || !tableBody) return;

                    const headerCells = Array.from(headerRow.children);
                    const columnIndex = headerCells.indexOf(headerCell);
                    if (columnIndex < 0) return;

                    const sortableRows = Array.from(tableBody.querySelectorAll(':scope > tr'))
                        .filter((row) => !row.querySelector('td[colspan], th[colspan]'))
                        .filter((row) => row.children.length === headerCells.length);

                    if (sortableRows.length < 2) return;

                    const shouldSortDescending = !headerCell.classList.contains('table-sort-desc');
                    headerCells.forEach((cell) => cell.classList.remove('table-sort-asc', 'table-sort-desc'));
                    headerCell.classList.add(shouldSortDescending ? 'table-sort-desc' : 'table-sort-asc');

                    sortableRows
                        .sort((leftRow, rightRow) => {
                            const leftValue = leftRow.children[columnIndex]?.innerText?.trim() || '';
                            const rightValue = rightRow.children[columnIndex]?.innerText?.trim() || '';
                            return compareSortableValues(leftValue, rightValue, shouldSortDescending);
                        })
                        .forEach((row) => tableBody.appendChild(row));
                };

                const hydrateSortableTableHeaders = () => {
                    document.querySelectorAll('#app table thead th').forEach((headerCell) => {
                        if (headerCell.dataset.sortHydrated === 'true') return;

                        const label = String(headerCell.textContent || '').trim().toLowerCase();
                        if (!label || nonSortableHeaderLabels.has(label) || headerCell.hasAttribute('colspan')) return;

                        headerCell.dataset.sortHydrated = 'true';
                        headerCell.classList.add('table-sortable');
                        headerCell.addEventListener('click', () => sortTableDomRows(headerCell));
                    });
                };
@endverbatim
