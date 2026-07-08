/**
 * keep-barang.js - Keep Barang Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF and Excel export for the Keep Barang menu.
 *
 * @module keep-barang
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function ensureXLSX() {
  if (window.XLSX) return Promise.resolve();
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src =
      'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js';
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('Gagal memuat library Excel'));
    document.head.appendChild(script);
  });
}

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export async function exportKeepBarangToExcel(deps) {
  const {
    rows,
    normalizeTypeHpValue,
    showNotification,
    notifyError,
  } = deps;

  await ensureXLSX();

  try {
    if (!rows.length) {
      showNotification('Tidak ada data untuk diekspor');
      return;
    }

    const exportData = rows.map((row, idx) => ({
      No: idx + 1,
      'Tgl Keep': row.TANGGAL_KEEP || '',
      Nama: row.NAMA || '',
      'No HP': row.NOMOR_HP || '',
      'No HP 2': row.NOMOR_HP_2 || '',
      'Type HP': normalizeTypeHpValue(row.TYPE_HP) || '',
      IMEI: row.IMEI_FULL || '',
      DP: row.DP_UANG_MUKA || 0,
      'Harga Jual': row.HARGA_JUAL || 0,
      'Rencana Ambil': row.RENCANA_PENGAMBILAN || '',
      'Handle By': row.HANDLE_BY || '',
      'Kasir By': row.KASIR_BY || '',
      'Team Gudang': row.TEAM_GUDANG || '',
      'Deadline Gudang': row.DEADLINE_TEAM_GUDANG || '',
      Status: row.STATUS || '',
      'Tgl Expired': row.TANGGAL_EXPIRED || '',
      'Sisa Hari': row.SISA_HARI_PENGAMBILAN || '',
    }));

    const workbook = window.XLSX.utils.book_new();
    window.XLSX.utils.book_append_sheet(
      workbook,
      window.XLSX.utils.json_to_sheet(exportData),
      'Retur Barang',
    );
    window.XLSX.writeFile(
      workbook,
      `Retur_Barang_${new Date().toISOString().split('T')[0]}.xlsx`,
    );
    showNotification(`${rows.length} data berhasil diunduh`);
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}

export function exportKeepBarangToPDF(deps) {
  const {
    rows,
    normalizeTypeHpValue,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    const activeRows = (rows || []).filter(
      (row) => row.STATUS !== 'DONE' && row.STATUS !== 'CANCEL',
    );

    if (!activeRows.length) {
      showNotification('Tidak ada data aktif (PENDING) untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    const bodyRows = activeRows
      .map(
        (row, idx) => `
            <tr>
                <td>${idx + 1}</td><td>${esc(row.TANGGAL_KEEP || '-')}</td>
                <td>${esc(row.NAMA || '-')}</td><td>${esc(row.NOMOR_HP || '-')}</td>
                <td>${esc(normalizeTypeHpValue(row.TYPE_HP) || '-')}</td><td>${esc(row.IMEI_FULL || '-')}</td>
                <td>${esc(row.RENCANA_PENGAMBILAN || '-')}</td><td>${esc(row.SISA_HARI_PENGAMBILAN || '-')}</td>
                <td>${esc(row.HANDLE_BY || '-')}</td><td>${esc(row.STATUS || '-')}</td>
            </tr>`,
      )
      .join('');

    const html = getPrintHTML({
      title: 'Keep Barang',
      subtitle: 'PURA PURA PONSEL - CUSTOMER SERVICE',
      period: `Dicetak Pada: ${today} | Status: PENDING`,
      totalLabel: 'Total Data',
      totalValue: String(activeRows.length),
      headers: [
        '#',
        'Tgl Keep',
        'Nama',
        'No HP',
        'Type HP',
        'IMEI',
        'Rencana Ambil',
        'Sisa Hari',
        'Handle By',
        'Status',
      ],
      rows: bodyRows,
      extraStyles:
        '<style>@page { size: A4 landscape; margin: 10mm 12mm; } table { font-size: 7.5pt; } th, td { padding: 3px; }</style>',
    });

    openPrintWindow(html, 'Retur Barang Ditahan', {
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
