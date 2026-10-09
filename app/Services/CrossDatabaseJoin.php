<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CrossDatabaseJoin
{
    public static function joinFromPppSource(string $table, array $select = ['*'], ?string $alias = null): Builder
    {
        $builder = DB::connection('ppp_source')->table($table);
        if ($alias) {
            $builder = $builder->as($alias);
        }

        return $builder->select($select);
    }

    public static function getAllFromPppSource(string $table, array $select = ['*'], array $where = []): array
    {
        $query = static::joinFromPppSource($table, $select);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->get()->toArray();
    }

    public static function getFromPppSourceWithJoin(
        string $localTable,
        string $remoteTable,
        string $localKey,
        string $remoteKey,
        array $localSelect = ['*'],
        array $remoteSelect = ['*'],
        array $whereLocal = [],
        array $whereRemote = []
    ): array {
        $local = DB::table($localTable)->select($localSelect);

        foreach ($whereLocal as $column => $value) {
            $local->where($column, $value);
        }

        $localData = $local->get()->toArray();

        if (empty($localData)) {
            return [];
        }

        $keys = array_column($localData, $localKey);
        $remote = DB::connection('ppp_source')
            ->table($remoteTable)
            ->select($remoteSelect)
            ->whereIn($remoteKey, $keys);

        foreach ($whereRemote as $column => $value) {
            $remote->where($column, $value);
        }

        $remoteData = $remote->get()->toArray();

        $result = [];
        foreach ($localData as $local) {
            $localId = $local->{$localKey};
            $matches = array_filter($remoteData, fn ($r) => $r->{$remoteKey} == $localId);

            if (! empty($matches)) {
                $local->remote = array_values($matches);
            }
            $result[] = $local;
        }

        return $result;
    }
}
