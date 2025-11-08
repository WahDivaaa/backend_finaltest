<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sorting = $request->input('sorting', 'popularity');
        $keyword = $request->input('keyword');

        $cacheTTL = now()->addMinutes(10);
        $cacheKey = 'authors.index.' . $sorting . '.' . md5($keyword ?? '');

        // Cache hanya data, bukan view
        $authors = Cache::remember($cacheKey, $cacheTTL, function () use ($sorting, $keyword) {
            $now = Carbon::now();
            $recentStart = $now->copy()->subDays(30);
            $lastStart = $now->copy()->subDays(60);

            $query = Authors::query()
                ->select('authors.id', 'authors.name')
                ->addSelect([
                    'total_ratings_count' => function ($q) {
                        $q->selectRaw('COUNT(ratings.id)')
                            ->from('ratings')
                            ->join('books', 'ratings.book_id', '=', 'books.id')
                            ->whereColumn('books.author_id', 'authors.id');
                    },
                    'overall_avg_rating' => function ($q) {
                        $q->selectRaw('AVG(ratings.rate)')
                            ->from('ratings')
                            ->join('books', 'ratings.book_id', '=', 'books.id')
                            ->whereColumn('books.author_id', 'authors.id');
                    },
                    'popularity_count' => function ($q) {
                        $q->selectRaw('COUNT(ratings.id)')
                            ->from('ratings')
                            ->join('books', 'ratings.book_id', '=', 'books.id')
                            ->whereColumn('books.author_id', 'authors.id')
                            ->where('ratings.rate', '>', 5);
                    },
                ])
                ->selectRaw("
                COALESCE((( 
                    (SELECT AVG(ratings.rate) FROM ratings 
                     INNER JOIN books ON ratings.book_id = books.id 
                     WHERE books.author_id = authors.id AND ratings.created_at >= ?) - 
                    (SELECT AVG(ratings.rate) FROM ratings 
                     INNER JOIN books ON ratings.book_id = books.id 
                     WHERE books.author_id = authors.id AND ratings.created_at BETWEEN ? AND ?)
                ) *
                (SELECT COUNT(ratings.id) FROM ratings 
                 INNER JOIN books ON ratings.book_id = books.id 
                 WHERE books.author_id = authors.id AND ratings.created_at >= ?)), 0) as trending_score
            ", [$recentStart, $lastStart, $recentStart, $recentStart]);

            switch ($sorting) {
                case 'average_rating':
                    $query->orderByDesc('overall_avg_rating');
                    break;
                case 'trending':
                    $query->orderByDesc('trending_score');
                    break;
                default:
                    $query->orderByDesc('popularity_count');
                    break;
            }

            return $query->take(20)->get();
        });

        // Render view di luar cache
        return view('author', compact('authors', 'sorting'));
    }

    public function getBooks($id)
    {
        $author = Authors::with('books:id,title,author_id')->find($id);

        if (!$author) {
            return response()->json([], 404);
        }

        return response()->json($author->books);
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
    public function show(Authors $authors)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Authors $authors)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Authors $authors)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Authors $authors)
    {
        //
    }
}
