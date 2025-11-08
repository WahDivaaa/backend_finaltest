<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use App\Models\Books;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     * Optimized for 500k+ records
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $selectedCategories = $request->input('category', []);
        $authorId = $request->input('author');
        $status = $request->input('status');
        $location = $request->input('location');
        $yearMin = $request->input('year_min');
        $yearMax = $request->input('year_max');
        $ratingMin = $request->input('rating_min');
        $ratingMax = $request->input('rating_max');
        $sorting = $request->input('sorting');

        $C = Cache::remember('global_avg_rating', 3600, fn() => DB::table('ratings')->avg('rate') ?? 0);
        $m = 50;
        $sevenDaysAgo = now()->subDays(7)->toDateTimeString();

        // Gunakan CTE (Common Table Expression) untuk performa maksimal
        $ratingStats = DB::table('ratings')
            ->select([
                'book_id',
                DB::raw('AVG(rate) as avg_rate'),
                DB::raw('COUNT(*) as rating_count'),
                DB::raw("AVG(CASE WHEN created_at >= '{$sevenDaysAgo}' THEN rate END) as recent_avg"),
                DB::raw("AVG(CASE WHEN created_at < '{$sevenDaysAgo}' THEN rate END) as historical_avg")
            ])
            ->groupBy('book_id');

        $books = Books::query()
            ->select([
                'books.id',
                'books.title',
                'books.isbn',
                'books.publisher',
                'books.year',
                'books.status',
                'books.location',
                'books.author_id',
                DB::raw('COALESCE(r.avg_rate, 0) as ratings_avg_rate'),
                DB::raw('COALESCE(r.rating_count, 0) as ratings_count'),
                DB::raw("((COALESCE(r.rating_count, 0) / (COALESCE(r.rating_count, 0) + {$m})) * COALESCE(r.avg_rate, 0)) + (({$m} / (COALESCE(r.rating_count, 0) + {$m})) * {$C}) AS weighted_rating"),
                DB::raw("CASE 
                    WHEN COALESCE(r.recent_avg, 0) > COALESCE(r.historical_avg, 0) THEN 1 
                    WHEN COALESCE(r.recent_avg, 0) < COALESCE(r.historical_avg, 0) THEN -1 
                    ELSE 0 
                END as trend_direction")
            ])
            ->leftJoinSub($ratingStats, 'r', 'books.id', '=', 'r.book_id');

        // Filter dengan index-friendly queries
        if (!empty($keyword)) {
            $books->where(function ($query) use ($keyword) {
                $searchTerm = $keyword . '%'; // Prefix search untuk index
                $query->where('books.title', 'like', $searchTerm)
                    ->orWhere('books.isbn', 'like', $searchTerm)
                    ->orWhere('books.publisher', 'like', $searchTerm)
                    ->orWhereIn('books.author_id', function ($subQuery) use ($searchTerm) {
                        $subQuery->select('id')
                            ->from('authors')
                            ->where('name', 'like', $searchTerm);
                    });
            });
        }

        // whereExists lebih cepat dari whereHas untuk large dataset
        if (!empty($selectedCategories)) {
            $books->whereExists(function ($query) use ($selectedCategories) {
                $query->select(DB::raw(1))
                    ->from('book_category')
                    ->whereColumn('book_category.book_id', 'books.id')
                    ->whereIn('book_category.category_id', $selectedCategories);
            });
        }

        // Filter sederhana dengan index
        if (!empty($authorId)) {
            $books->where('books.author_id', $authorId);
        }
        if (!empty($status)) {
            $books->where('books.status', $status);
        }
        if (!empty($location)) {
            $books->where('books.location', $location);
        }

        // Year filter - gunakan index
        if (!empty($yearMin) && !empty($yearMax)) {
            $books->whereBetween('books.year', [$yearMin, $yearMax]);
        } elseif (!empty($yearMin)) {
            $books->where('books.year', '>=', $yearMin);
        } elseif (!empty($yearMax)) {
            $books->where('books.year', '<=', $yearMax);
        }

        // CRITICAL: Filter rating SEBELUM sorting untuk performa
        // Tambahkan ke WHERE dengan subquery instead of HAVING
        if (!empty($ratingMin) || !empty($ratingMax)) {
            $books->where(function($query) use ($ratingMin, $ratingMax) {
                if (!empty($ratingMin) && !empty($ratingMax)) {
                    $query->whereBetween(DB::raw('COALESCE(r.avg_rate, 0)'), [$ratingMin, $ratingMax]);
                } elseif (!empty($ratingMin)) {
                    $query->where(DB::raw('COALESCE(r.avg_rate, 0)'), '>=', $ratingMin);
                } elseif (!empty($ratingMax)) {
                    $query->where(DB::raw('COALESCE(r.avg_rate, 0)'), '<=', $ratingMax);
                }
            });
        }

        // Sorting optimization
        switch ($sorting) {
            case 'total_votes':
                $books->orderByDesc('ratings_count')
                      ->orderBy('books.id'); // Tambah secondary sort untuk consistency
                break;
            case 'recent_popularity':
                // Pre-calculate dalam subquery untuk performa
                $books->addSelect(DB::raw("(SELECT COUNT(*) FROM ratings WHERE ratings.book_id = books.id AND created_at >= NOW() - INTERVAL 30 DAY) as recent_ratings_count"))
                    ->orderByDesc('recent_ratings_count')
                    ->orderBy('books.id');
                break;
            case 'alphabetical':
                $books->orderBy('books.title', 'asc')
                      ->orderBy('books.id');
                break;
            case 'weighted_average_rating':
            default:
                $books->orderByDesc('weighted_rating')
                      ->orderBy('books.id');
                break;
        }

        // Gunakan cursorPaginate untuk dataset besar (lebih efisien dari offset)
        // Tapi karena blade sudah pakai paginate, tetap pakai paginate
        $books = $books->paginate(50);

        // Eager load relasi HANYA untuk current page
        $books->load(['author:id,name', 'category:id,name']);

        // Cache dropdown lists dengan duration panjang
        $categories = Cache::remember('categories_dropdown', 7200, function () {
            return Categories::select('id', 'name')->orderBy('name')->get();
        });

        $authors = Cache::remember('authors_dropdown', 7200, function () {
            return Authors::select('id', 'name')->orderBy('name')->get();
        });

        return view('home', compact('books', 'categories', 'authors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(books $books)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(books $books)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, books $books)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(books $books)
    {
        //
    }
}