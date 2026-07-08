/**
 * price-comparison.js - Price Comparison Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for Price Comparison.
 *
 * @module price-comparison
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportPriceComparisonToPDF(deps) {
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
      showNotification('Tidak ada data harga untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

    const bodyRows = rows
      .map(
        (row, idx) => `
            <tr>
                <td>${idx + 1}</td><td>${esc(row.Nama_Produk) || '-'}</td><td>${esc(row.Tanggal_Cek) || '-'}</td>
                <td class="text-right">${formatCurrency(row.Harga_Distributor_1 || 0)}</td>
                <td class="text-right">${formatCurrency(row.Harga_Distributor_2 || 0)}</td>
                <td class="text-right" style="color:#2563eb;font-weight:bold;">${formatCurrency(row.Harga_Kompetitor || 0)}</td>
                <td class="text-right" style="color:#1d4ed8;font-weight:bold;">${formatCurrency(row.Harga_Rencana_Jual || 0)}</td>
                <td class="text-right" style="color:#1e40af;font-weight:bold;">${formatCurrency(row.Margin_Profit || 0)}</td>
                <td class="text-right" style="color:${(row.Selisih || 0) > 0 ? '#57534e' : '#a8a29e'};font-weight:bold;">${formatCurrency(row.Selisih || 0)}</td>
            </tr>`,
      )
      .join('');

    const html = getPrintHTML({
      title: 'ANALISIS HARGA & KOMPETITOR',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today} | ${rows.length} Produk`,
      totalLabel: 'Total Produk',
      totalValue: `${rows.length} Produk`,
      headers: [
        '#',
        'Produk',
        'Tgl Cek',
        'Dist 1',
        'Dist 2',
        'Kompetitor',
        'Rencana Jual',
        'Margin',
        'Selisih',
      ],
      rows: bodyRows,
    });

    openPrintWindow(html, 'Harga Kompetitor', {
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
