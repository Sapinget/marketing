/**
 * dashboard-exports-all.js - Aggregator: imports all export bridges
 *
 * Built as IIFE via vite.export.config.js.
 * Each bridge sets window.* globals synchronously on load.
 *
 * @module dashboard-exports-all
 */

// Core print helpers -> window.getPrintHTML, window.openPrintWindow, etc.
import './print-core.js';
import './print-browser.js';

// Per-tab export bridges -> window.MarketingDashboard*
import './analytics-export-bridge.js';
import './customer-service-bridge.js';
import './sales-export-bridge.js';
import './reporting-export-bridge.js';
