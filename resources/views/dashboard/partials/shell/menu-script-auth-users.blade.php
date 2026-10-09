@verbatim
                const optionLabel = (options, value, fallback = 'Pilih') => {
                    const list = Array.isArray(options?.value) ? options.value : (Array.isArray(options) ? options : []);
                    const found = list.find((opt) => {
                        const optValue = opt && typeof opt === 'object' ? opt.value : opt;
                        return String(optValue) === String(value);
                    });
                    if (found === undefined) return fallback;
                    return found && typeof found === 'object' ? (found.label ?? found.value ?? fallback) : found;
                };

                Object.assign(menuExports, {
                    optionLabel,
                });
@endverbatim
