<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MasterPlanDistributionSync
{
    public static function sync(object $row, CarbonInterface|string|null $now = null): array
    {
        $now = $now ? Carbon::parse($now) : now();
        $today = $now->toDateString();
        $stats = [
            'distribution_upserts' => 0,
            'analytics_upserts' => 0,
            'invalid_json' => 0,
            'skipped_without_meta' => 0,
            'skipped_not_published' => 0,
        ];

        if (strtoupper(trim((string) $row->status)) !== 'PUBLISHED') {
            self::deleteDerivedRows($row);
            $stats['skipped_not_published']++;

            return $stats;
        }

        $metaRaw = self::blankToNull($row->distribution_meta);
        if ($metaRaw === null) {
            self::deleteDerivedRows($row);
            $stats['skipped_without_meta']++;

            return $stats;
        }

        $meta = json_decode($metaRaw, true);
        if (! is_array($meta)) {
            self::deleteDerivedRows($row);
            $stats['invalid_json']++;

            return $stats;
        }

        $distributionPlatforms = [];
        $analyticsPlatforms = [];

        foreach ($meta as $platform => $detail) {
            if (! is_array($detail)) {
                continue;
            }

            $platform = trim((string) $platform);
            if ($platform === '') {
                continue;
            }

            $tanggalPublish = self::normalizeDate($detail['date'] ?? $row->tanggal_rencana);
            $rawPayload = json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            DB::table('distributions')->updateOrInsert(
                [
                    'master_id' => $row->source_id,
                    'platform' => $platform,
                ],
                [
                    'master_plan_id' => $row->id ?? null,
                    'title' => self::blankToNull($row->title),
                    'tanggal_publish' => $tanggalPublish,
                    'link' => self::blankToNull($detail['link'] ?? null),
                    'type' => self::blankToNull($detail['type'] ?? ($meta['contentType'] ?? null)),
                    'raw_payload' => $rawPayload,
                    'converted_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                    'updated_by_user_id' => self::normalizeUserId($row->updated_by_user_id ?? null),
                    'created_by_user_id' => self::normalizeUserId($row->created_by_user_id ?? $row->updated_by_user_id ?? null),
                ],
            );
            $distributionPlatforms[] = $platform;
            $stats['distribution_upserts']++;

            if ($tanggalPublish !== null && $tanggalPublish <= $today) {
                DB::table('analytics')->updateOrInsert(
                    [
                        'master_id' => $row->source_id,
                        'platform' => $platform,
                    ],
                    [
                        'master_plan_id' => $row->id ?? null,
                        'title' => self::blankToNull($row->title),
                        'tanggal_publish' => $tanggalPublish,
                        'raw_payload' => $rawPayload,
                        'converted_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                        'updated_by_user_id' => self::normalizeUserId($row->updated_by_user_id ?? null),
                        'created_by_user_id' => self::normalizeUserId($row->created_by_user_id ?? $row->updated_by_user_id ?? null),
                    ],
                );
                $analyticsPlatforms[] = $platform;
                $stats['analytics_upserts']++;
            }
        }

        self::deleteMissingPlatforms('distributions', $row, $distributionPlatforms);
        self::deleteMissingPlatforms('analytics', $row, $analyticsPlatforms);

        return $stats;
    }

    public static function deleteDerivedRows(object|string $masterPlan): void
    {
        self::buildDerivedRowsQuery('analytics', $masterPlan)->delete();
        self::buildDerivedRowsQuery('distributions', $masterPlan)->delete();
    }

    private static function deleteMissingPlatforms(string $table, object|string $masterPlan, array $platforms): void
    {
        $query = self::buildDerivedRowsQuery($table, $masterPlan);

        if ($platforms === []) {
            $query->delete();

            return;
        }

        $query->whereNotIn('platform', array_values(array_unique($platforms)))->delete();
    }

    private static function buildDerivedRowsQuery(string $table, object|string $masterPlan)
    {
        $masterPlanId = is_object($masterPlan) ? self::normalizeUserId($masterPlan->id ?? null) : null;
        $sourceId = is_object($masterPlan)
            ? self::blankToNull($masterPlan->source_id ?? null)
            : self::blankToNull($masterPlan);

        return DB::table($table)->where(function ($query) use ($masterPlanId, $sourceId): void {
            if ($masterPlanId !== null) {
                $query->where('master_plan_id', $masterPlanId);
            }

            if ($sourceId !== null) {
                if ($masterPlanId !== null) {
                    $query->orWhere('master_id', $sourceId);
                } else {
                    $query->where('master_id', $sourceId);
                }
            }
        });
    }

    private static function blankToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function normalizeDate(mixed $value): ?string
    {
        $value = self::blankToNull($value);
        if ($value === null) {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : $value;
    }

    private static function normalizeUserId(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
