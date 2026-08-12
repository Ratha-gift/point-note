<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'NotoKhmer';
            src: url('{{ storage_path('fonts/NotoSansKhmer-Regular.ttf') }}');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'NotoKhmer', sans-serif;
            font-size: 12px;
            color: #1f2937;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
        }
        td.num, th.num {
            text-align: right;
        }
        tfoot td {
            font-weight: bold;
            background-color: #fffbeb;
        }
        .point {
            color: #b45309;
        }
    </style>
</head>
<body>
    <h1>សរុប Point ប្រចាំខែ</h1>
    <div class="subtitle">{{ $month }}</div>

    <table>
        <thead>
            <tr>
                <th>ថ្ងៃ</th>
                <th class="num">សរុប ($)</th>
                <th class="num">Point</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td class="num">${{ number_format($row['total'], 2) }}</td>
                    <td class="num point">{{ $row['point'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">មិនទាន់មានទិន្នន័យខែនេះទេ</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>សរុប Point ខែនេះ</td>
                <td class="num">${{ number_format($monthTotal, 2) }}</td>
                <td class="num point">{{ $monthPoint }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
