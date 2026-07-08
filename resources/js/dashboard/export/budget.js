/**
 * budget.js - Budget Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for Budgeting.
 *
 * @module budget
 */

import { getPrintBaseStyles, getPrintOrgHeaderHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

export function exportBudgetToPDF(deps) {
  const {
    config,
    calculations,
    formatCurrency,
    formatNumber,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    const cfg = config;
    const calc = calculations;
    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    const safe = (value) => Number(value) || 0;

    const adsRows = `
            <tr><td><strong>Meta Ads</strong></td>
                <td class="text-center">${cfg.meta.totalAds} Slot</td>
                <td class="text-center">${cfg.meta.days} Hari</td>
                <td class="text-right">${formatCurrency(calc.metaTotal)}</td>
                <td class="text-right" style="color:#a8a29e;">${formatCurrency(Math.min(calc.metaTotal, safe(cfg.meta.balance)))}</td>
                <td class="text-right font-bold">${formatCurrency(calc.metaTopup)}</td></tr>
            <tr><td><strong>Google Ads</strong></td>
                <td class="text-center">${cfg.google.totalAds} Slot</td>
                <td class="text-center">${cfg.google.days} Hari</td>
                <td class="text-right">${formatCurrency(calc.googleTotal)}</td>
                <td class="text-right" style="color:#a8a29e;">${formatCurrency(Math.min(calc.googleTotal, safe(cfg.google.balance)))}</td>
                <td class="text-right font-bold">${formatCurrency(calc.googleTopup)}</td></tr>`;

    const mekariRows = `
            <tr><td><strong>Mekari Visitor</strong><div style="font-size:7pt;color:#a8a29e;">Target: ${cfg.mekari.visitor.targetPerDay}/hari x ${cfg.mekari.visitor.days} hari</div></td>
                <td class="text-center">${formatNumber(calc.mekariVisitorTotal)} Unit</td>
                <td class="text-right">${formatNumber(Math.min(calc.mekariVisitorTotal, safe(cfg.mekari.visitor.balance)))} Unit</td>
                <td class="text-right font-bold" style="color:#dc2626;">${calc.mekariVisitorNeeded > 0 ? `${formatNumber(calc.mekariVisitorNeeded)} Unit` : '-'}</td>
                <td class="text-right font-bold">${formatCurrency(safe(cfg.mekari.visitor.topupCost))}</td></tr>
            <tr><td><strong>Mekari Broadcast</strong><div style="font-size:7pt;color:#a8a29e;">Durasi: ${cfg.mekari.broadcast.weeks} minggu</div></td>
                <td class="text-center">-</td><td class="text-center">-</td><td class="text-center">-</td>
                <td class="text-right font-bold">${formatCurrency(calc.mekariBroadcastTopup)}</td></tr>`;

    let otherRows = '';
    if ((calc.colabBreakdown || []).length) {
      otherRows += `<tr><td colspan="5" style="background:#f5f5f4;font-weight:bold;font-size:8pt;">COLLABORATION & PARTNERSHIP</td></tr>`;
      calc.colabBreakdown.forEach((item) => {
        otherRows += `<tr><td>${item.name}</td><td class="text-center">${item.slots} Slot</td>
                    <td class="text-right">${formatCurrency(item.packageCost || 0)}</td>
                    <td class="text-right" style="color:#a8a29e;">-</td>
                    <td class="text-right font-bold">${formatCurrency(item.packageCost || 0)}</td></tr>`;
      });
    }
    if ((calc.othersCalculated || []).length) {
      otherRows += `<tr><td colspan="5" style="background:#f5f5f4;font-weight:bold;font-size:8pt;">OPERATIONAL & OTHERS</td></tr>`;
      calc.othersCalculated.forEach((item) => {
        otherRows += `<tr><td>${item.name}</td>
                    <td class="text-center">${item.quantity} ${item.unit} x ${item.duration} Hari</td>
                    <td class="text-right">${formatCurrency(item.total)}</td>
                    <td class="text-right" style="color:#a8a29e;">${formatCurrency(Math.min(item.total, item.balance || 0))}</td>
                    <td class="text-right font-bold">${formatCurrency(item.topup)}</td></tr>`;
      });
    }
    if (!otherRows) {
      otherRows =
        '<tr><td colspan="5" class="text-center" style="color:#a8a29e;font-style:italic;">Tidak ada pengeluaran lain-lain.</td></tr>';
    }

    const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Budget Plan</title>
            ${getPrintBaseStyles()}
            <style>
                .section-title { font-size:10pt; font-weight:bold; margin:18px 0 8px 0; padding-bottom:4px; border-bottom:1px solid #eee; }
            </style></head><body>
            ${getPrintOrgHeaderHTML()}
            <h1>BUDGETING PLAN & APPROVAL</h1>
            <table style="width:100%;border-collapse:collapse;border:1px solid #e7e5e4;background:#f5f5f4;margin-bottom:20px;page-break-inside:avoid;">
                <tr>
                    <td style="padding:15px;text-align:center;border:none;border-right:1px solid #e7e5e4;width:50%;">
                        <div style="font-size:8pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.5px;">Total Rancangan Biaya</div>
                        <div style="font-size:12pt;font-weight:bold;color:#111;margin-top:5px;">${formatCurrency(calc.metaTotal + calc.googleTotal + calc.mekariTopupTotal + (calc.othersCalculated || []).reduce((sum, item) => sum + item.total, 0))}</div>
                    </td>
                    <td style="padding:15px;text-align:center;border:none;width:50%;">
                        <div style="font-size:8pt;font-weight:bold;color:#666;text-transform:uppercase;letter-spacing:.5px;">Total Top-Up Required</div>
                        <div style="font-size:12pt;font-weight:bold;color:#dc2626;margin-top:5px;">${formatCurrency(calc.totalTopup)}</div>
                    </td>
                </tr>
            </table>
            <div class="section-title">PLATFORM ADS (META & GOOGLE)</div>
            <table><thead><tr><th width="30%">Platform</th><th>Target/Slot</th><th>Durasi</th>
                <th class="text-right">Estimasi Biaya</th><th class="text-right">Saldo Dipakai</th>
                <th class="text-right">Top-up Need</th></tr></thead>
                <tbody>${adsRows}</tbody></table>
            <div class="section-title">MEKARI ECOSYSTEM</div>
            <table><thead><tr><th width="40%">Item</th><th>Total Kebutuhan</th>
                <th class="text-right">Saldo Unit</th><th class="text-right">Kekurangan</th>
                <th class="text-right">Biaya Top-up</th></tr></thead>
                <tbody>${mekariRows}</tbody></table>
            <div class="section-title">COLLABORATION & OTHERS</div>
            <table><thead><tr><th width="40%">Item</th><th>Qty / Durasi</th>
                <th class="text-right">Total Biaya</th><th class="text-right">Saldo Dipakai</th>
                <th class="text-right">Top-up Need</th></tr></thead>
                <tbody>${otherRows}</tbody></table>
            <div class="signature-section">
                <div class="signature-block"><div class="signature-line"></div>
                    <div style="font-size:8pt;font-weight:bold;">Prepared By</div>
                    <div style="font-size:7pt;color:#666;">Team Marketing</div></div>
                <div class="signature-block"><div class="signature-line"></div>
                    <div style="font-size:8pt;font-weight:bold;">Approved By</div>
                    <div style="font-size:7pt;color:#666;">Finance / Manager</div></div>
            </div></body></html>`;

    openPrintWindow(html, 'Budget Plan', {
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
