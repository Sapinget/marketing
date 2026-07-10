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

                // Story Form
                const storyTab = ref("Ganjil");
                const storyModalOpen = ref(false);
                const storyModalType = ref("create");
                const storyForm = ref({
                    ID: null,
                    Tanggal: "",
                    Jam: "09:00",
                    Story: "",
                    Catatan: "",
                    Link: "",
                    is_genap: "Ganjil",
                    Status: ""
                });
@endverbatim
