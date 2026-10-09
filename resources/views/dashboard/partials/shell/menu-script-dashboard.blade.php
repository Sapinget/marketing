@verbatim
                const dashboardTodayLabel = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());

                const dashboardGreeting = computed(() => {
                    const hour = new Date().getHours();
                    const waktu = hour < 11 ? 'pagi' : hour < 15 ? 'siang' : hour < 19 ? 'sore' : 'malam';
                    const nama = String(currentUser.value?.nama || currentUser.value?.username || '').trim() || 'Tim';
                    return `Selamat ${waktu}, ${nama}`;
                });

                const publishedPlanCount = computed(() => (masterPlanData.value || []).filter(i => i.Status === 'PUBLISHED').length);

                Object.assign(menuExports, {
                    dashboardTodayLabel,
                    dashboardGreeting,
                    publishedPlanCount,
                });
@endverbatim
