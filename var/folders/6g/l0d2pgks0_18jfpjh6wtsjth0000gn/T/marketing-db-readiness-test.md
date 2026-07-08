# MySQL Migration Readiness Report

Generated at: 2026-07-07 02:39:18

## Status

- status: `ready_for_mysql_structure`
- db_connection: `sqlite`
- db_database: `:memory:`

## Table Totals

- `users`: 1
- `master_plans`: 0
- `distributions`: 0
- `analytics`: 0
- `lpjk`: 0
- `lpjk_detail`: 0
- `activity_logs`: 0

## Blocking Checks

- `distributions_missing_master_plan_id`: 0
- `analytics_missing_master_plan_id`: 0
- `lpjk_detail_missing_lpjk_id`: 0
- `distributions_orphan_master_plan_id`: 0
- `analytics_orphan_master_plan_id`: 0
- `lpjk_detail_orphan_lpjk_id`: 0
- `activity_logs_orphan_user_id`: 0

## Legacy Compatibility Checks

- `distributions_blank_master_id`: 0
- `analytics_blank_master_id`: 0
- `lpjk_detail_blank_master_id`: 0
- `master_plans_blank_created_by`: 0
- `master_plans_blank_updated_by`: 0

## Notes

- SQLite masih aktif. MySQL belum dikonfigurasi selama DB_CONNECTION belum diubah ke mysql.
- Migrasi ke MySQL aman dilakukan setelah blocker relasi bernilai 0.
- Kolom legacy string boleh tetap ada sementara, tetapi write-path utama sekarang harus mengandalkan foreign key internal.
