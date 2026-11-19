@php use App\Support\FieldPresenter; @endphp
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body>
    <h1>@if(! empty($definition['code'])) መዝገብ {{ $definition['code'] }} — @endif{{ $definition['title'] }}</h1>
    @if ($records->isEmpty())
        <p>አሁን የተመዘገበ መረጃ የለም።</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ FieldPresenter::heading($definition, $column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        @foreach ($columns as $column)
                            <td>{{ FieldPresenter::show($record, $column) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
