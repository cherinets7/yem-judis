@php
    use App\Enums\ReviewStatus;
@endphp
<div class="d-flex flex-wrap gap-2">
    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.'.$definition['uri'].'.edit', $record) }}">አርም</a>

    @if ($canReview && $definition['review'] && $record->status !== ReviewStatus::Approved)
        <form method="post" action="{{ route('admin.'.$definition['uri'].'.approve', $record) }}">
            @csrf
            @if ($definition['note'])
                <input class="form-control form-control-sm mb-1" name="review_note" placeholder="የግምገማ ማስታወሻ" maxlength="2000">
            @endif
            <button class="btn btn-sm btn-success" type="submit">አጽድቅ</button>
        </form>
    @endif

    @if ($canReview && $definition['review'] && $record->status !== ReviewStatus::Rejected)
        <form method="post" action="{{ route('admin.'.$definition['uri'].'.reject', $record) }}">
            @csrf
            <button class="btn btn-sm btn-outline-danger" type="submit">አትቀበል</button>
        </form>
    @endif

    @if ($definition['publish'])
        <form method="post" action="{{ route('admin.'.$definition['uri'].'.publish', $record) }}">
            @csrf
            <input type="hidden" name="is_published" value="{{ $record->is_published ? '0' : '1' }}">
            <button class="btn btn-sm btn-outline-success" type="submit">{{ $record->is_published ? 'ከህዝብ ደብቅ' : 'ለህዝብ አትም' }}</button>
        </form>
    @endif

    <form method="post" action="{{ route('admin.'.$definition['uri'].'.destroy', $record) }}" onsubmit="return confirm('ይህ መዝገብ ይሰረዝ?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit">ሰርዝ</button>
    </form>
</div>
