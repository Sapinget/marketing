<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Tables</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 32px;
            background: #f5f7fb;
            color: #18212f;
        }
        .wrap {
            max-width: 960px;
            margin: 0 auto;
        }
        h1 {
            margin: 0 0 8px;
        }
        p {
            margin: 0 0 24px;
            color: #516074;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(24, 33, 47, 0.08);
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e7ebf1;
        }
        th {
            background: #18212f;
            color: #fff;
        }
        tr:last-child td {
            border-bottom: 0;
        }
        a {
            color: #0b57d0;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .count {
            width: 140px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>MySQL Tables</h1>
        <p>Database: <strong>{{ $database }}</strong></p>

        <table>
            <thead>
                <tr>
                    <th>Table</th>
                    <th class="count">Rows</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tables as $table)
                    <tr>
                        <td>
                            <a href="{{ url('/__db/tables/'.rawurlencode($table['name'])) }}">{{ $table['name'] }}</a>
                        </td>
                        <td>{{ number_format($table['count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
