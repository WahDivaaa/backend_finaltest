<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        $sorting = $request->input('sorting', 'popularity');
        $keyword = $request->input('keyword');

        $cacheKey = 'authors_index_sort_' . $sorting . '_keyword_' . $keyword;
        $cacheTTL = 600;

        $authors = Cache::remember($cacheKey, $cacheTTL, function () use ($sorting, $keyword) {
            return $this->getAuthorsQuery($sorting, $keyword)->get();
        });
        return view('author', compact('authors', 'sorting'));
    }

    private function getAuthorsQuery($sorting, $keyword = null)
    {
        $now = Carbon::now();
        $recentStart = $now->copy()->subDays(30)->toDateTimeString();
        $lastStart = $now->copy()->subDays(60)->toDateTimeString();

        $query = Authors::query()
            ->select([
                'authors.id',
                'authors.name',
                DB::raw('COUNT(DISTINCT r.id) as total_ratings_count'),
                DB::raw('COALESCE(AVG(r.rate), 0) as overall_avg_rating'),
                DB::raw('COUNT(DISTINCT CASE WHEN r.rate > 5 THEN r.id END) as popularity_count'),
                DB::raw("COALESCE(
                    (AVG(CASE WHEN r.created_at >= ? THEN r.rate END) - AVG(CASE WHEN r.created_at BETWEEN ? AND ? THEN r.rate END)) *
                    COUNT(DISTINCT CASE WHEN r.created_at >= ? THEN r.id END),0) as trending_score")
            ])
            ->leftJoin('books as b', 'authors.id', '=', 'b.author_id')
            ->leftJoin('ratings as r', 'b.id', '=', 'r.book_id')
            ->groupBy('authors.id', 'authors.name');

        $query->addBinding([
            $recentStart, 
            $lastStart,   
            $recentStart, 
            $recentStart  
        ], 'select');

        if (!empty($keyword)) {
            $query->where('authors.name', 'like', "%{$keyword}%");
        }

        switch ($sorting) {
            case 'average_rating':
                $query->orderByDesc('overall_avg_rating') ;
                break;
            case 'trending':
                $query->orderByDesc('trending_score');
                break;
            case 'popularity':
            default:
                $query->orderByDesc('popularity_count');
                break;
        }
        $query->having('total_ratings_count', '>', 0);

        return $query->limit(20);
    }

    // public function getBooks($id)
    // {
    //     $author = Authors::select('id', 'name')
    //         ->with(['books' => function ($query) {
    //             $query->select('id', 'title', 'author_id', 'isbn', 'year')
    //                 ->orderBy('year', 'desc');
    //         }])
    //         ->find($id);

    //     if (!$author) {
    //         return response()->json(['message' => 'Author not found'], 404);
    //     }

    //     return response()->json([
    //         'author' => $author->name,
    //         'books' => $author->books
    //     ]);
    // }

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
