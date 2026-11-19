<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body>
    <h1>ዐቃቤ ህጎች እና ፐብሊክ ሰርቫንት</h1>
    @if ($profiles->isEmpty())
        <p>አሁን የተመዘገበ መረጃ የለም።</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ተቋም</th>
                    <th>መደብ</th>
                    <th>ሙሉ ስም</th>
                    <th>የሥራ መደብ</th>
                    <th>የአሁን ደመወዝ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profiles as $profile)
                    <tr>
                        <td>{{ \App\Support\FieldPresenter::show($profile, 'organization_id') }}</td>
                        <td>{{ \App\Support\FieldPresenter::show($profile, 'staff_category') }}</td>
                        <td>{{ $profile->full_name }}</td>
                        <td>{{ $profile->job_title ?: '—' }}</td>
                        <td>{{ $profile->current_salary ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
