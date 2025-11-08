@extends('layouts.app')

@section('content')
<div class="container max-w-lg mx-auto mt-6">
    <h2 class="text-xl font-bold mb-4">Add Rating</h2>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rating.store') }}">
        @csrf
        <div class="mb-3">
            <label for="author_id" class="block font-semibold mb-1">Author</label>
            <select name="author_id" id="author_id" class="border p-2 w-full rounded" required>
                <option value="">-- Select Author --</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="book_id" class="block font-semibold mb-1">Book</label>
            <select name="book_id" id="book_id" class="border p-2 w-full rounded" required>
                <option value="">-- Select Book --</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="rate" class="block font-semibold mb-1">Rating</label>
            <select name="rate" id="rate" class="border p-2 w-full rounded" required>
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <button type="submit" class="btn btn-primary py-2 px-4 rounded">
            Submit
        </button>
    </form>
</div>

<script>
document.getElementById('author_id').addEventListener('change', function () {
    const authorId = this.value;
    const bookSelect = document.getElementById('book_id');
    bookSelect.innerHTML = '<option>Loading...</option>';

    fetch(`/author/${authorId}/books`)
        .then(res => res.json())
        .then(data => {
            bookSelect.innerHTML = '<option value="">-- Select Book --</option>';
            data.forEach(book => {
                bookSelect.innerHTML += `<option value="${book.id}">${book.title}</option>`;
            });
        })
        .catch(() => {
            bookSelect.innerHTML = '<option>Error loading books</option>';
        });
});
</script>
@endsection
