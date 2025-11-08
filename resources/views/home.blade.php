@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">📚 Daftar Buku</h2>
        <form class="d-flex w-50" role="search">
            <input class="form-control me-1" type="search" name="keyword" placeholder="Cari Judul/Author/ISBN/Publisher"
                aria-label="Search" />
            <button class="btn btn-primary" type="submit">Cari</button>
        </form>
    </div>
    <form>
        <div class="card shadow-sm border-0 mb-2 p-4">
            <div class="row g-3">
                <div class="d-flex justify-content-between">
                    <h4 class="me-4">Filter</h4>
                    <a class="btn btn-outline-primary" href="{{ route('books.index') }}">Reset</a>
                </div>
                {{-- Kategori Dropdown --}}
                <div class="col dropdown">
                    <label for="year">Kategori</label>
                    <button class="w-100 btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCategory"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Pilih Kategori
                    </button>
                    <div class="dropdown-menu p-2" style="max-height:300px; overflow-y:auto;"
                        aria-labelledby="dropdownCategory">
                        @foreach ($categories as $category)
                            <div class="form-check">
                                <input class="form-check-input category-checkbox" name="category[]" type="checkbox"
                                    value="{{ $category->id }}" id="category">
                                <label class="form-check-label" for="category">{{ $category->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- Dropdown Author --}}
                <div class="col">
                    <label for="author">Author</label>
                    <div class="w-100 mt-0">
                        <select id="author" name="author" class="btn btn-outline-secondary text-center w-100">
                            <option value="">Cari Author</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" class="text-start">{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Year Range --}}
                <div class="col">
                    <label for="year">Tahun terbit</label>
                    <div class="d-flex">
                        <input class="form-control me-2" type="number" name="year_min" placeholder="Min"
                            aria-label=".form-control example">
                        <input class="form-control" type="number" name="year_max" placeholder="Max"
                            aria-label=".form-control example">
                    </div>
                </div>
                {{-- Status --}}
                <div class="col">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="mt-0 btn btn-outline-secondary text-center w-100">
                        <option value="">Cari Status</option>
                        <option class="text-start" value="available">Available</option>
                        <option class="text-start" value="rented">Rented</option>
                        <option class="text-start" value="reserved">Reserved</option>
                    </select>
                </div>
                {{-- Location --}}
                <div class="col">
                    <label for="location">Lokasi Toko</label>
                    <select id="location" name="location" class="mt-0 btn btn-outline-secondary text-center w-100">
                        <option value="">Cari Lokasi</option>
                        <option class="text-start" value="Badung">Badung</option>
                        <option class="text-start" value="Bangli">Bangli</option>
                        <option class="text-start" value="Buleleng">Buleleng</option>
                        <option class="text-start" value="Denpasar">Denpasar</option>
                        <option class="text-start" value="Gianyar">Gianyar</option>
                        <option class="text-start" value="Jembrana">Jembrana</option>
                        <option class="text-start" value="Tabanan">Tabanan</option>
                    </select>
                </div>
                {{-- Rating --}}
                <div class="col">
                    <label for="year">Rating buku</label>
                    <div class="d-flex">
                        <input class="form-control me-2" type="number" name="rating_min" placeholder="Min rating"
                            aria-label=".form-control example">
                        <input class="form-control" type="number" name="rating_max" placeholder="Max rating"
                            aria-label=".form-control example">
                    </div>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-lg-3">
                    <label for="sorting">Sort By</label>
                    <select id="sorting" name="sorting" class="mt-0 btn btn-outline-secondary text-center w-100">
                        <option value="weighted_rating" selected>Weighted average rating</option>
                        <option class="text-start" value="total_votes">Total votes</option>
                        <option class="text-start" value="recent_popularity">Recent popularity (last 30 days)</option>
                        <option class="text-start" value="alphabetical">Alphabetical</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="filter"></label>
                    <button type="submit" class="w-100 btn btn-primary">Filter</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Tabel buku --}}
    <div class="card shadow-sm border-0 ">
        <div class="card-body">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th scope="col" style="width: 5%;">#</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Author</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Tahun</th>
                        <th scope="col">ISBN</th>
                        <th scope="col">Publisher</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Voter</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = $books->firstItem() @endphp
                    @forelse ($books as $book)
                        <tr>
                            <td class="text-center">{{ $i++ }} 
                                @if ($book->trend_direction == 1)
                                    <span style="color: green; font-weight: bold;">▲</span>
                                @elseif ($book->trend_direction == -1)
                                    <span style="color: red; font-weight: bold;">▼</span>
                                @else
                                    <span style="color: grey; font-weight: bold;">-</span>
                                @endif</td>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author->name ?? '-' }}</td>
                            <td>{{ $book->category->name ?? '-' }}</td>
                            <td class="text-center">{{ $book->year }}</td>
                            <td class="text-center">{{ $book->isbn }}</td>
                            <td>{{ $book->publisher }}</td>
                            <td class="text-center">{{ number_format($book->ratings_avg_rate, 1) }}</td>
                            <td class="text-center">{{ $book->ratings_count }}</td>
                            <td class="text-center">{{ $book->location }}</td>
                            <td class="text-center">
                                @php
                                    $color = match ($book->status) {
                                        'available' => 'success',
                                        'rented' => 'warning',
                                        'reserved' => 'secondary',
                                        default => 'light',
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($book->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $books->links() }}
        </div>
    </div>
@endsection
