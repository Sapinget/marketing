<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $table }} Preview</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 32px;
            background: #f5f7fb;
            color: #18212f;
        }
        .wrap {
            max-width: 1400px;
            margin: 0 auto;
        }
        .topbar {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0;
        }
        p {
            margin: 6px 0 0;
            color: #516074;
        }
        a {
            color: #0b57d0;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .table-wrap {
            overflow: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(24, 33, 47, 0.08);
        }
        table {
            min-width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e7ebf1;
            vertical-align: top;
            white-space: nowrap;
        }
        th {
            position: sticky;
            top: 0;
            background: #18212f;
            color: #fff;
        }
        td {
            max-width: 320px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .empty {
            padding: 24px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(24, 33, 47, 0.08);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <div>
                <h1>{{ $table }}</h1>
                <p>Showing first {{ $rows->count() }} rows of {{ number_format($totalRows) }} total rows.</p>
            </div>
            <a href="{{ url('/__db/tables') }}">Back to tables</a>
        </div>

        @if ($rows->isEmpty())
            <div class="empty">This table has no rows.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                @foreach ($columns as $column)
                                    <td>{{ is_scalar(data_get($row, $column)) || data_get($row, $column) === null ? data_get($row, $column) : json_encode(data_get($row, $column), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
