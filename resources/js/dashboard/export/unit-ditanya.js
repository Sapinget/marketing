/**
 * unit-ditanya.js - Unit Ditanya Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF and Excel export for the Unit Ditanya menu.
 *
 * @module unit-ditanya
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';
import { ensureXLSX } from './xlsx-loader.js';

// ----------------------------------------------
// Data helpers
// ----------------------------------------------

/**
 * Group raw unit-ditanya rows by Brand|Seri|Tipe key.
 * Each group sums DITANYA and counts TERSEDIA status.
 *
 * @param {Array<Object>} rows - Raw data rows
 * @returns {Array<{Brand:string,Seri:string,Tipe:string,Ditanya:number,Tersedia:number}>}
 */
export function buildUnitDitanyaGrouped(rows) {
  const map = new Map();
  rows.forEach((row) => {
    const brand = String(row.BRAND || '').trim();
    const seri = String(row.SERI || '').trim();
    const tipe = String(row.TIPE || row['TYPE UNIT'] || '').trim();
    const key = `${brand}|${seri}|${tipe}`;
    if (!key.replace(/\|/g, '')) return;
    const existing = map.get(key) || {
      Brand: brand,
      Seri: seri,
      Tipe: tipe,
      Ditanya: 0,
      Tersedia: 0,
    };
    existing.Ditanya += Number(row.DITANYA || 0) || 1;
    existing.Tersedia +=
      String(row.AVAILABLE || '').toUpperCase() === 'TERSEDIA' ? 1 : 0;
    map.set(key, existing);
  });
  return Array.from(map.values()).sort((a, b) => b.Ditanya - a.Ditanya);
}

// ----------------------------------------------
// Excel export
// ----------------------------------------------

/**
 * Export filtered unit-ditanya data to an Excel workbook.
 * Includes 3 sheets: Grouped, Detail, Summary.
 *
 * @param {object} deps
 * @param {Array<Object>} deps.rows         - Filtered data rows (raw)
 * @param {{start:string,end:string}} deps.dateRange
 * @param {string} deps.availableFilter     - Current AVAILABLE filter value
 * @param {string} deps.search              - Current search query
 * @param {Function} deps.showNotification
 * @param {Function} deps.notifyError
 * @returns {Promise<void>}
 */
export async function exportUnitDitanyaToExcel(deps) {
  const {
    rows,
    dateRange,
    availableFilter,
    search,
    showNotification,
    notifyError,
  } = deps;

  await ensureXLSX();

  try {
    if (!rows.length) {
      showNotification('Tidak ada data untuk diekspor');
      return;
    }

    const grouped = buildUnitDitanyaGrouped(rows);

    // Sheet 1: Grouped
    const exportData = grouped.map((g, idx) => ({
      No: idx + 1,
      Brand: g.Brand,
      Seri: g.Seri,
      Tipe: g.Tipe,
      'Total Ditanya': g.Ditanya,
      'Stock Tersedia': g.Tersedia,
    }));
    const wb = window.XLSX.utils.book_new();
    const ws = window.XLSX.utils.json_to_sheet(exportData);
    window.XLSX.utils.book_append_sheet(wb, ws, 'Unit Ditanya');

    // Sheet 2: Detail
    const detailData = rows.map((row, idx) => ({
      No: idx + 1,
      Tanggal: row.TANGGAL || '',
      Kategori: row.KATEGORI || '',
      Brand: row.BRAND || '',
      Seri: row.SERI || '',
      Tipe: row.TIPE || row['TYPE UNIT'] || '',
      RAM: row.RAM || '',
      Internal: row.INTERNAL || '',
      Kondisi: row.KONDISI || '',
      Available: row.AVAILABLE || '',
    }));
    window.XLSX.utils.book_append_sheet(
      wb,
      window.XLSX.utils.json_to_sheet(detailData),
      'Detail',
    );

    // Sheet 3: Summary
    window.XLSX.utils.book_append_sheet(
      wb,
      window.XLSX.utils.aoa_to_sheet([
        ['Total Data (Detail)', rows.length],
        ['Total Data (Grouped)', grouped.length],
        [
          'Periode',
          `${dateRange.start || '-'} -> ${dateRange.end || '-'}`,
        ],
        ['Available Filter', availableFilter || 'Semua'],
        ['Search', search || '-'],
      ]),
      'Summary',
    );

    const today = new Date().toISOString().split('T')[0];
    window.XLSX.writeFile(wb, `Unit_Ditanya_${today}.xlsx`);
    showNotification(
      `${grouped.length} produk dari ${rows.length} data berhasil diunduh`,
    );
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}

// ----------------------------------------------
// PDF export
// ----------------------------------------------

/**
 * Export filtered unit-ditanya data to a PDF print window.
 * Builds a grouped table layout with A4 landscape orientation.
 *
 * @param {object} deps
 * @param {Array<Object>} deps.rows         - Filtered data rows (raw)
 * @param {{start:string,end:string}} deps.dateRange
 * @param {string} deps.availableFilter     - Current AVAILABLE filter value
 * @param {string} deps.search              - Current search query
 * @param {Function} deps.formatNumber      - Number formatter (locale-aware)
 * @param {Function} deps.showNotification
 * @param {Function} deps.notifyError
 * @returns {void}
 */
export function exportUnitDitanyaToPDF(deps) {
  const {
    rows,
    dateRange,
    availableFilter,
    search,
    formatNumber,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data unit ditanya untuk dicetak');
      return;
    }

    const grouped = buildUnitDitanyaGrouped(rows);
    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    const esc = (v) =>
      String(v ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const bodyRows = grouped
      .map(
        (g, idx) =>
          `<tr>
              <td>${idx + 1}</td>
              <td>${esc(g.Brand || '-')} ${esc(g.Seri || '')}</td>
              <td>${esc(g.Tipe || '-')}</td>
              <td class="text-right">${esc(formatNumber(g.Ditanya))}</td>
              <td class="text-right">${esc(formatNumber(g.Tersedia))}</td>
            </tr>`,
      )
      .join('');

    const period = [
      `Periode: ${dateRange.start || '-'} -> ${dateRange.end || '-'}`,
      `Available: ${availableFilter || 'Semua'}`,
      `Search: ${search || '-'}`,
    ].join(' | ');

    const html = getPrintHTML({
      title: 'UNIT DITANYA',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today} | ${period}`,
      totalLabel: 'Total Data (Grouped)',
      totalValue: formatNumber(grouped.length),
      headers: [
        '#',
        'Brand & Seri',
        'Tipe',
        'Total Ditanya',
        'Stock Tersedia',
      ],
      rows: bodyRows,
      extraStyles:
        '<style>@page { size: A4 landscape; margin: 10mm; } table { font-size: 6.5pt; } th, td { padding: 3px; }</style>',
      showSignature: true,
    });

    openPrintWindow(html, 'Unit Ditanya', {
      showNotification,
      notifyError,
      jsonApi,
      getFriendlyErrorMessage,
      resolveAppUrl,
    });
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}
