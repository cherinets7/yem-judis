@extends('layouts.public')

@section('title', 'የጉዳዮች አስተዳደር')

@section('content')
    <div class="mb-4">
        <h1 class="section-title mb-1">የጉዳዮች አስተዳደር</h1>
        <p class="meta mb-0">በዐቃቤ ህግ መዝገብ፣ በፍርድ ቤት መዝገብ፣ በከሳሽ ወይም በተከሳሽ ስም ይፈልጉ። የታተሙ ጉዳዮች ብቻ ይታያሉ።</p>
    </div>

    <form class="filter-bar mb-4" method="get" action="{{ route('cases.tracking') }}">
        <div class="row g-3 align-items-end">
            <div class="col-lg-6">
                <label class="form-label" for="q">ፍለጋ</label>
                <input class="form-control" id="q" name="q" value="{{ $search }}" placeholder="መዝገብ ቁጥር፣ የከሳሽ ወይም የተከሳሽ ስም">
            </div>
            <div class="col-lg-4">
                <label class="form-label" for="case_type">የጉዳይ ዓይነት</label>
                <select class="form-select" id="case_type" name="case_type">
                    <option value="">ሁሉም</option>
                    @foreach ($caseTypes as $type)
                        <option value="{{ $type->value }}" @selected($caseType === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-green" type="submit">ፈልግ</button>
            </div>
        </div>
    </form>

    <p class="meta">{{ $cases->total() }} ጉዳዮች ተገኝተዋል</p>

    @if ($cases->isEmpty())
        <div class="empty-state">ከዚህ ፍለጋ ጋር የሚዛመድ የታተመ ጉዳይ አልተገኘም።</div>
    @else
        <div class="table-wrap d-none d-lg-block">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>የዐቃቤ ህግ መዝገብ</th>
                            <th>የፍርድ ቤት መዝገብ</th>
                            <th>ከሳሽ</th>
                            <th>ተከሳሽ</th>
                            <th>ዓይነት</th>
                            <th>ፍርድ ቤት</th>
                            <th>ቀጠሮ</th>
                            <th>ውጤት</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cases as $case)
                            <tr>
                                <td>{{ $case->prosecutor_file_number }}</td>
                                <td>{{ $case->court_file_number ?: '—' }}</td>
                                <td>{{ $case->plaintiff_name ?: '—' }}</td>
                                <td>{{ $case->defendant_name ?: '—' }}</td>
                                <td><span class="badge badge-green">{{ $case->case_type->label() }}</span></td>
                                <td>{{ $case->court_level->label() }}</td>
                                <td>
                                    {{ $case->appointment_date_ec ?: '—' }}
                                    @if ($case->appointment_reason)
                                        <div class="meta">{{ $case->appointment_reason }}</div>
                                    @endif
                                </td>
                                <td>{{ $case->final_outcome->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-lg-none">
            @foreach ($cases as $case)
                <article class="portal-card mb-3">
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span class="badge badge-green">{{ $case->case_type->label() }}</span>
                        <span class="badge badge-gold">{{ $case->final_outcome->label() }}</span>
                    </div>
                    <h2>{{ $case->plaintiff_name ?: 'ከሳሽ አልተመዘገበም' }} / {{ $case->defendant_name ?: 'ተከሳሽ አልተመዘገበም' }}</h2>
                    <p class="mb-1">የዐቃቤ ህግ መዝገብ፡ {{ $case->prosecutor_file_number }}</p>
                    <p class="mb-1">የፍርድ ቤት መዝገብ፡ {{ $case->court_file_number ?: '—' }}</p>
                    <p class="mb-1">{{ $case->court_level->label() }} · {{ $case->organization->amharicName() }}</p>
                    <p class="mb-0 meta">ቀጠሮ፡ {{ $case->appointment_date_ec ?: 'አልተቀጠረም' }}</p>
                </article>
            @endforeach
        </div>

        @include('public.partials.pager', ['paginator' => $cases])
    @endif
@endsection
