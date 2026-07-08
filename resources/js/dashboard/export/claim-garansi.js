/**
 * claim-garansi.js - Claim Garansi & Asuransi Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b)
 * Handles PDF and Excel export for Claim Garansi & Asuransi menu.
 *
 * @module claim-garansi
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

// ----------------------------------------------
// Helpers
// ----------------------------------------------

/**
 * Dynamically load the SheetJS (XLSX) library from CDN if not already loaded.
 *
 * @returns {Promise<void>}
 */
function ensureXLSX() {
  if (window.XLSX) return Promise.resolve();
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src =
      'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js';
    s.onload = () => resolve();
    s.onerror = () => reject(new Error('Gagal memuat library Excel'));
    document.head.appendChild(s);
  });
}

/**
 * Escape HTML special characters for safe string interpolation.
 *
 * @param {*} v
 * @returns {string}
 */
function esc(v) {
  return String(v ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// ----------------------------------------------
// Excel export
// ----------------------------------------------

/**
 * Export filtered claim-garansi data to an Excel workbook.
 * Includes 2 sheets: Claim, Summary.
 *
 * @param {object} deps
 * @param {Array<Object>} deps.rows         - Filtered data rows (raw)
 * @param {string} deps.statusFilter        - Current status filter value
 * @param {string} deps.garansiFilter       - Current garansi filter value
 * @param {string} deps.search              - Current search query
 * @param {Function} deps.showNotification
 * @param {Function} deps.notifyError
 * @returns {Promise<void>}
 */
export async function exportClaimGaransiToExcel(deps) {
  const {
    rows,
    statusFilter,
    garansiFilter,
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

    // Sheet 1: Claim detail
    const exportData = rows.map((row, idx) => ({
      No: idx + 1,
      'Nama Customer': row.NAMA_CUSTOMER || '',
      'No Service': row.NO_SERVICE || '',
      'No Transaksi': row.NO_TRANSAKSI || '',
      'Tanggal Masuk': row.TANGGAL_MASUK || '',
      'Tanggal Estimasi': row.TANGGAL_ESTIMASI || '',
      'Tanggal Diambil': row.TANGGAL_DIAMBIL || '',
      'WA Customer': row.WA_CUSTOMER || '',
      Tipe: row.TIPE || '',
      IMEI: row.IMEI || '',
      Seri: row.SERI || '',
      Model: row.MODEL || '',
      Status: row.STATUS || '',
      'Lokasi Klaim': row.LOKASI_KLAIM || '',
      Garansi: row.GARANSI || '',
      Kerusakan: row.KERUSAKAN || '',
      Keterangan: row.KETERANGAN || '',
    }));

    const wb = window.XLSX.utils.book_new();
    const ws = window.XLSX.utils.json_to_sheet(exportData);
    window.XLSX.utils.book_append_sheet(wb, ws, 'Claim');

    // Sheet 2: Summary
    const summaryRows = [
      ['Total Data', rows.length],
      ['Filter Status', statusFilter || 'Semua'],
      ['Filter Garansi', garansiFilter || 'Semua'],
      ['Search', search || '-'],
    ];
    window.XLSX.utils.book_append_sheet(
      wb,
      window.XLSX.utils.aoa_to_sheet(summaryRows),
      'Summary',
    );

    const today = new Date().toISOString().split('T')[0];
    window.XLSX.writeFile(wb, `Claim_Garansi_Asuransi_${today}.xlsx`);
    showNotification(`${rows.length} data berhasil diunduh`);
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}

// ----------------------------------------------
// PDF export
// ----------------------------------------------

/**
 * Export active claim-garansi data (not yet picked up) to a PDF print window.
 * Builds a table with 11 columns in A4 landscape orientation.
 *
 * @param {object} deps
 * @param {Array<Object>} deps.rows           - All raw data rows
 * @param {string} deps.statusFilter          - Current status filter value
 * @param {string} deps.lokasiFilter          - Current lokasi filter value
 * @param {string} deps.garansiFilter         - Current garansi filter value
 * @param {Function} deps.formatNumber        - Number formatter (locale-aware)
 * @param {Function} deps.showNotification
 * @param {Function} deps.notifyError
 * @param {Function} deps.jsonApi
 * @param {Function} deps.getFriendlyErrorMessage
 * @param {Function} deps.resolveAppUrl
 * @returns {void}
 */
export function exportClaimGaransiToPDF(deps) {
  const {
    rows,
    statusFilter,
    lokasiFilter,
    garansiFilter,
    formatNumber,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    // Only show active claims (not yet picked up)
    const activeRows = (rows || []).filter(
      (r) => !String(r.TANGGAL_DIAMBIL || '').trim(),
    );

    if (!activeRows.length) {
      showNotification(
        'Tidak ada data claim aktif (belum diambil) untuk dicetak',
      );
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    const bodyRows = activeRows
      .map(
        (r, idx) =>
          `<tr>
            <td>${idx + 1}</td>
            <td>${esc(r.NAMA_CUSTOMER || '-')}</td>
            <td>${esc(r.NO_SERVICE || '-')}</td>
            <td>${esc(r.NO_TRANSAKSI || '-')}</td>
            <td>${esc(r.TANGGAL_MASUK || '-')}</td>
            <td>${esc(r.TIPE || '-')}</td>
            <td>${esc(r.IMEI || '-')}</td>
            <td>${esc(r.LOKASI_KLAIM || '-')}</td>
            <td>${esc(r.STATUS || '-')}</td>
            <td>${esc(r.GARANSI || '-')}</td>
            <td>${esc(r.KERUSAKAN || '-')}</td>
          </tr>`,
      )
      .join('');

    const period = [
      `Status: ${statusFilter || 'Semua'}`,
      `Lokasi: ${lokasiFilter || 'Semua'}`,
      `Garansi: ${garansiFilter || 'Semua'}`,
    ].join(' | ');

    const html = getPrintHTML({
      title: 'CLAIM GARANSI & ASURANSI',
      subtitle: 'PURA PURA PONSEL - AFTER SALES',
      period: `Dicetak Pada: ${today} | ${period}`,
      totalLabel: 'Total Data',
      totalValue: formatNumber(activeRows.length),
      headers: [
        '#',
        'Nama Customer',
        'No Service',
        'No Transaksi',
        'Tgl Masuk',
        'Tipe',
        'IMEI',
        'Lokasi Klaim',
        'Status',
        'Garansi',
        'Kerusakan',
      ],
      rows: bodyRows,
      extraStyles:
        '<style>@page { size: A4 landscape; margin: 10mm 12mm; } table { font-size: 7pt; } th, td { padding: 3px; }</style>',
      showSignature: true,
    });

    openPrintWindow(html, 'Claim Garansi', {
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
