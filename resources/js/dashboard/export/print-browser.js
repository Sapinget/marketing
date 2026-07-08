/**
 * print-browser.js - Browser Print Adapter
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b)
 * Handles browser-native print flow: submit HTML to backend,
 * open popup, navigate to served document.
 *
 * @module print-browser
 */

import { buildStandalonePrintHtml } from './print-core.js';

// ----------------------------------------------
// Submit print HTML to backend
// ----------------------------------------------

/**
 * POST print HTML to `/print-job` endpoint and return the response.
 *
 * @param {string} printHtml - Complete HTML document string
 * @param {object} deps
 * @param {Function} deps.jsonApi - API caller (url, options) => Promise
 * @returns {Promise<{token?: string, ok?: boolean, error?: string}>}
 */
export async function submitBrowserPrintJob(printHtml, { jsonApi }) {
  return jsonApi('/print-job', {
    method: 'POST',
    body: JSON.stringify({ html: printHtml }),
  });
}

// ----------------------------------------------
// Open print window
// ----------------------------------------------

/**
 * Open a popup window, submit the print HTML to the backend,
 * and navigate the popup to the served document URL.
 *
 * Popup flow:
 *   1. Open blank popup with "Menyiapkan" message
 *   2. POST auto-print HTML to `/print-job` -> get token
 *   3. Navigate popup to `/print-job/{token}` (real HTTP document)
 *   4. Backend-served document ensures Chrome respects `print-color-adjust: exact`
 *
 * @param {string}  html          - Print HTML fragment (without wrapper)
 * @param {string}  [reportName]  - Optional report name (for UX, not currently used)
 * @param {object}  deps
 * @param {Function} deps.jsonApi
 * @param {Function} deps.showNotification
 * @param {Function} deps.notifyError
 * @param {Function} deps.getFriendlyErrorMessage
 * @param {Function} deps.resolveAppUrl
 * @param {Function} [deps.buildStandalonePrintHtmlFn=buildStandalonePrintHtml]
 * @returns {void}
 */
export function openPrintWindow(html, reportName, deps) {
  const {
    jsonApi,
    showNotification,
    notifyError,
    getFriendlyErrorMessage,
    resolveAppUrl,
    buildStandalonePrintHtmlFn = buildStandalonePrintHtml,
  } = deps;

  const autoPrintHtml = buildStandalonePrintHtmlFn(html, { autoPrint: true });

  // Open blank popup
  const pw = window.open('', '_blank', 'width=1200,height=1000');
  if (!pw || pw.closed) {
    showNotification('Izinkan popup untuk print PDF');
    return;
  }

  const setPopupStatus = (msg) => {
    try {
      if (!pw.closed) {
        const p = pw.document.querySelector('p');
        if (p) p.textContent = msg;
      }
    } catch (e) {
      // cross-origin or closed - safe to ignore
    }
  };

  // Show initial loading message
  try {
    pw.document.open();
    pw.document.write([
      '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>',
      '<body style="font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0">',
      '<p style="color:#64748b;font-size:14px">[1/2] Menyiapkan dokumen print...</p>',
      '</body></html>',
    ].join(''));
    pw.document.close();
  } catch (e) {
    // popup may have been closed
  }

  submitBrowserPrintJob(autoPrintHtml, { jsonApi })
    .then((result) => {
      const token = String((result && result.token) || '').trim();
      if (!token) {
        throw new Error(
          getFriendlyErrorMessage(
            result,
            'Backend tidak mengembalikan token print.',
          ),
        );
      }
      const backendUrl = new URL(
        resolveAppUrl('/print-job'),
        window.location.origin,
      ).origin.replace(/\/+$/, '');
      setPopupStatus('[2/2] Dokumen siap - membuka print...');
      pw.location.href = [
        backendUrl,
        '/print-job/',
        encodeURIComponent(token),
      ].join('');
    })
    .catch((err) => {
      setPopupStatus(
        `[error] ${getFriendlyErrorMessage(
          err,
          'Dokumen print harus dibuka dari backend print-job.',
        )}`,
      );
      notifyError(
        'Print gagal',
        err,
        'Dokumen print harus dibuka dari backend print-job.',
      );
    });
}

// ----------------------------------------------
// Global exports for inline script compatibility
// ----------------------------------------------

if (typeof window !== 'undefined') {
  window.submitBrowserPrintJob = submitBrowserPrintJob;
  window.openPrintWindow = openPrintWindow;
}
