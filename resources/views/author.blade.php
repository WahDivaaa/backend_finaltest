@extends('layouts.app')

@section('content')

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $sorting == 'popularity' ? 'active' : '' }}"
                href="{{ url('/author?sorting=popularity') }}">By Popularity</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $sorting == 'average_rating' ? 'active' : '' }}"
                href="{{ url('/author?sorting=average_rating') }}">By Average Rating</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $sorting == 'trending' ? 'active' : '' }}"
                href="{{ url('/author?sorting=trending') }}">Trending</a>
        </li>
    </ul>

    {{-- Tabel Author --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th scope="col" style="width: 3%;">#</th>
                        <th>Author</th>
                        <th>Total Ratings</th>
                        <th>Avg. Rating</th>
                        <th>Popularity (Voters > 5)</th>
                        <th>Best Rated Book</th>
                        <th>Worst Rated Book</th>
                        <th>Trending Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($authors as $author)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $author->name }}</td>

                            <td class="text-center">{{ $author->total_ratings_count }}</td>
                            <td class="text-center">{{ number_format($author->overall_avg_rating, 1) }}</td>
                            <td class="text-center">{{ $author->popularity_count }}</td>
                            <td>
                                {{ $author->best_rated_book->title }}
                            </td>
                            <td>
                                {{ $author->worst_rated_book->title }}
                            </td>
                            <td class="text-center">
                                @if ($author->trending_score > 0)
                                    <span
                                        style="color: green; font-weight: bold;">▲</span>{{ number_format($author->trending_score, 2) }}
                                @elseif ($author->trending_score < 0)
                                    <span
                                        style="color: red; font-weight: bold;">▼</span>{{ number_format(abs($author->trending_score), 2) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data author.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
