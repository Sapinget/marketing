@verbatim
                // Distribution Form
                const distModalOpen = ref(false);
                const distributionForm = ref({
                    ID: null,
                    Master_ID: "",
                    Judul: "",
                    Platform: "Instagram",
                    Tanggal_Publish: todayStr(),
                    Link: ""
                });

                // Analytics Form
                const analyticsModalOpen = ref(false);
                const analyticsForm = ref({
                    ID: null,
                    Master_ID: "",
                    Judul: "",
                    Platform: "Instagram",
                    Views: 0,
                    Likes: 0,
                    Comments: 0,
                    Shares: 0
                });

                // Story form state now comes from customer-service runtime helper.
@endverbatim
