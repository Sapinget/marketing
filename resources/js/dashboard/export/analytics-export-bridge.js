import { exportAnalyticsToPDF } from './analytics.js';
import { exportActiveTabPdf } from './active-tab-pdf.js';
import { exportGenericTabularPdf } from './generic-tabular.js';

window.MarketingDashboardAnalyticsExports = Object.freeze({
  exportActiveTabPdf,
  exportAnalyticsToPDF,
  exportGenericTabularPdf,
});
