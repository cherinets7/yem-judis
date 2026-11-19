@php use App\Support\ProsecutorWeeklyMatrix; @endphp
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body>
    @forelse ($reports as $index => $payload)
        @if ($index > 0)
            <pagebreak />
        @endif
        <h1>{{ $payload['title'] }}</h1>
        <p>
            {{ $payload['report']->organization?->amharicName() }}
            · የበጀት ዓመት {{ $payload['report']->budget_year_ec }}
            · ሳምንት {{ $payload['report']->week_number }}
            · {{ $payload['report']->status->label() }}
            @if ($payload['report']->period_covered_ec)
                · {{ $payload['report']->period_covered_ec }}
            @endif
        </p>
        <p>
            የማጥራት ምጣኔ {{ $payload['matrix']['summary']['clearance_rate'] === null ? '—' : $payload['matrix']['summary']['clearance_rate'].'%' }}
            · የመርታት ምጣኔ {{ $payload['matrix']['summary']['win_rate'] === null ? '—' : $payload['matrix']['summary']['win_rate'].'%' }}
            · የማስቀጣት ምጣኔ {{ $payload['matrix']['summary']['conviction_rate'] === null ? '—' : $payload['matrix']['summary']['conviction_rate'].'%' }}
        </p>
        @foreach ($payload['matrix']['sections'] as $section)
            <h2>{{ $section['title'] }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>ረድፍ</th>
                        @foreach ($section['columns'] as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($section['rows'] as $row)
                        <tr @class(['total' => $row['total']])>
                            <td>{{ $row['label'] }}</td>
                            @foreach ($section['columns'] as $column)
                                @php
                                    $value = $row['metrics'][$column['key']] ?? null;
                                    $text = $value === null
                                        ? '—'
                                        : (str_ends_with($column['key'], '_rate') ? number_format((float) $value, 1).'%' : $value);
                                @endphp
                                <td>{{ $text }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @empty
        <h1>{{ $emptyTitle ?? 'የዐቃቤ ህግ ሳምንታዊ ሪፖርት' }}</h1>
        <p>አሁን የተመዘገበ ሪፖርት የለም።</p>
    @endforelse
</body>
</html>
