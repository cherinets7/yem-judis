@extends('layouts.app')

@section('title', 'መልዕክቶች')

@section('content')
    <h1 class="h4 mb-3">መልዕክቶች</h1>
    <section class="page-card p-3 p-lg-4">
        @forelse ($notes as $note)
            <article class="d-flex flex-wrap justify-content-between gap-2 border-bottom py-2">
                <div>
                    <div class="{{ $note->read_at ? 'meta' : 'fw-bold' }}">{{ $note->data['title'] ?? 'መልዕክት' }}</div>
                    <div class="meta">{{ $note->data['body'] ?? '' }}</div>
                </div>
                <form method="post" action="{{ route('notifications.read', $note) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-success" type="submit">ክፈት</button>
                </form>
            </article>
        @empty
            <div class="empty-state mb-0">መልዕክት የለም።</div>
        @endforelse
        <div class="mt-3">@include('public.partials.pager', ['paginator' => $notes])</div>
    </section>
@endsection
