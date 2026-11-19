@foreach ($matrix['sections'] as $section)
    <section class="page-card p-3 mb-3">
        <h3 class="h5">{{ $section['title'] }}</h3>
        <div class="matrix-wrap">
            <table class="table matrix-table mb-0">
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
                        <tr @class(['is-total' => $row['total']])>
                            <th scope="row">{{ $row['label'] }}</th>
                            @foreach ($section['columns'] as $column)
                                @php
                                    $value = $row['metrics'][$column['key']] ?? null;
                                    $text = $value === null
                                        ? '—'
                                        : (str_ends_with($column['key'], '_rate') ? number_format((float) $value, 1).'%' : $value);
                                @endphp
                                <td>
                                    @if ($column['input'] && ! $row['total'])
                                        <input
                                            class="form-control form-control-sm matrix-input"
                                            type="number"
                                            min="0"
                                            max="1000000"
                                            step="1"
                                            inputmode="numeric"
                                            name="rows[{{ $section['code'] }}][{{ $row['code'] }}][{{ $column['key'] }}]"
                                            value="{{ (int) ($value ?? 0) }}"
                                            data-section="{{ $section['code'] }}"
                                            data-row="{{ $row['code'] }}"
                                            data-key="{{ $column['key'] }}"
                                            aria-label="{{ $section['title'] }} {{ $row['label'] }} {{ $column['label'] }}"
                                        >
                                    @else
                                        <span data-cell="{{ $section['code'] }}.{{ $row['code'] }}.{{ $column['key'] }}">{{ $text }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endforeach
