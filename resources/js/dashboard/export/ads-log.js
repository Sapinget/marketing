/**
 * ads-log.js - Ads Log Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for Ads Log.
 *
 * @module ads-log
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportAdsLogToPDF(deps) {
  const {
    rows,
    formatCurrency,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data ads untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    const totalBiaya = rows.reduce(
      (sum, row) => sum + (Number(row.Biaya) || 0),
      0,
    );

    let rowNo = 1;
    const rowsHtml = rows
      .map(
        (row) => `
            <tr>
                <td>${rowNo++}</td>
                <td class="font-bold">${esc(row.Nama || '-')}</td>
                <td style="font-family:monospace;">${esc(row.ID_Ads || '-')}</td>
                <td class="text-right">${(Number(row.Jangkauan) || 0).toLocaleString('id')}</td>
                <td class="text-center font-bold">${Number(row.Rata_Komentar) || 0}</td>
                <td class="text-center">${esc(row.Tanggal || '-')}</td>
                <td class="text-right">${formatCurrency(row.Biaya || 0)}</td>
                <td class="text-right font-bold" style="color:#059669;">${formatCurrency(row.Sisa_Saldo || 0)}</td>
                <td class="text-center">${esc(row.Kategori || '-')}</td>
            </tr>`,
      )
      .join('');

    const extraStyles = `<style>
            @page { size: A4 landscape; margin: 10mm; }
            table { font-size: 8pt; }
            th { text-transform: uppercase; }
            th:nth-child(1) { width: 30px; }
            th:nth-child(3) { width: 100px; }
            th:nth-child(4),
            th:nth-child(7),
            th:nth-child(8) { width: 100px; text-align: right; }
            th:nth-child(5),
            th:nth-child(6),
            th:nth-child(9) { text-align: center; }
            td { padding: 5px 4px; }
        </style>`;

    const html = getPrintHTML({
      title: 'ADS PERFORMANCE REPORT',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today}`,
      totalLabel: 'Total Biaya Ads',
      totalValue: formatCurrency(totalBiaya),
      headers: [
        'No',
        'Nama Campaign',
        'ID Ads',
        'Reach',
        'Score',
        'Tanggal',
        'Spent',
        'Saldo',
        'Kategori',
      ],
      rows: rowsHtml,
      extraStyles,
    });

    openPrintWindow(html, 'Ads Report', {
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
