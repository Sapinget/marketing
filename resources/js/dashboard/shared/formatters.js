export const formatNumber = (value) => new Intl.NumberFormat("id-ID").format(Number(value || 0));

export const formatCurrency = (value) => {
    const n = Number(value || 0);
    if (isNaN(n)) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
};

export const formatWaNumber = (phone) => {
    let s = String(phone || '');
    if (s.startsWith('0')) s = s.slice(1);
    return s.replace(/[^0-9]/g, '');
};

export const calcAdminPct = (row) => {
    if (row['ADMIN %'] || row.ADMIN_PCT) return row['ADMIN %'] || row.ADMIN_PCT;
    const h = Number(row['HARGA ONLINE'] || row.HARGA_ONLINE || 0);
    const c = Number(row['NOMINAL CAIR'] || row.NOMINAL_CAIR || 0);
    if (h > 0 && c >= 0) return ((h - c) / h * 100).toFixed(1) + '%';
    return '-';
};

export const formatShortDate = (dateStr) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    return date.toLocaleDateString("id-ID", { day: "2-digit", month: "short" });
};

export const formatFullDate = (dateStr) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return "-";
    return date.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
};

export const formatMonthLabel = (val) => {
    if (!val) return "";
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const [y, m] = val.split("-").map(Number);
    return `${monthNames[m - 1]} ${y}`;
};

export const getStatusColor = (status) => {
    const s = status?.toUpperCase();
    if (s === "NOT STARTED") return "bg-slate-100 text-slate-600 border border-slate-200";
    if (s === "PENDING") return "bg-amber-100 text-amber-600 border border-amber-200";
    if (s === "PROCESED" || s === "PROCESSED") return "bg-indigo-100 text-indigo-600 border border-indigo-200";
    if (s === "CLAIM") return "bg-emerald-100 text-emerald-600 border border-emerald-200";
    if (s === "DRAFT") return "bg-slate-100 text-slate-500";
    if (s === "ONGOING") return "bg-amber-100 text-amber-700";
    if (s === "SELESAI") return "bg-emerald-100 text-emerald-700";
    if (s === "CANCEL") return "bg-rose-100 text-rose-500";
    const sl = s?.toLowerCase();
    if (sl === "editing" || sl === "progres") return "bg-blue-100 text-blue-600";
    if (sl === "shooting") return "bg-amber-100 text-amber-600";
    if (sl === "ide") return "bg-slate-100 text-slate-600";
    if (sl === "done" || sl === "published") return "bg-emerald-100 text-emerald-600";
    return "bg-slate-100 text-slate-600";
};
