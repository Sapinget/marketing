@verbatim
                const fmtLocalDate = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                const todayStr = () => fmtLocalDate(new Date());
                const normalizeDateKey = (value) => {
                    if (!value) return '';
                    if (value instanceof Date) {
                        return isNaN(value.getTime()) ? '' : fmtLocalDate(value);
                    }
                    const raw = String(value).trim();
                    const match = raw.match(/^(\d{4}-\d{2}-\d{2})/);
                    if (match) return match[1];
                    const parsed = new Date(raw);
                    return isNaN(parsed.getTime()) ? '' : fmtLocalDate(parsed);
                };
                const pickDateKey = (...values) => {
                    for (const value of values) {
                        const normalized = normalizeDateKey(value);
                        if (normalized) return normalized;
                    }
                    return '';
                };
                const isDateInRange = (value, start, end) => {
                    const normalized = normalizeDateKey(value);
                    if (!normalized) return false;
                    if (start && normalized < start) return false;
                    if (end && normalized > end) return false;
                    return true;
                };
@endverbatim
