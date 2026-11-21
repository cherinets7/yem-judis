@extends('layouts.app')

@section('title', 'ፈቃዶች')

@section('content')
    <section class="page-card p-3 p-lg-4">
        <h1 class="h4 mb-1">ፈቃዶች</h1>
        <p class="meta mb-4">እያንዳንዱ ሚና የትኛውን ክፍል እንደሚጠቀም ይምረጡ። ዋና አስተዳዳሪ ሁልጊዜ ይገባል።</p>

        <form method="post" action="{{ route('permissions.update') }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ክፍል</th>
                            @foreach ($roles as $role)
                                <th>{{ $role->label() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group => $definition)
                            <tr>
                                <th scope="row">{{ $definition['label'] }}</th>
                                @foreach ($roles as $role)
                                    <td>
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="granted[{{ $group }}][]"
                                            value="{{ $role->value }}"
                                            aria-label="{{ $definition['label'] }} — {{ $role->label() }}"
                                            @checked(in_array($role->value, $granted[$group], true))
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success mt-4" type="submit">ፈቃዶቹን አስቀምጥ</button>
        </form>
    </section>
@endsection
