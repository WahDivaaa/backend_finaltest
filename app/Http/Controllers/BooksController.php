<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use App\Models\Books;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BooksController extends Controller
{
    public function index(Request $request)
    {
        $C = Cache::remember('global_avg_rating', 3600, fn() => \App\Models\Ratings::avg('rate') ?? 0);
        $m = 50;

        $books = Books::query()
            ->with(['author:id,name', 'category:id,name'])
            ->withBookStatistics($C, $m)
            ->applyFilters($request)
            ->applySorting($request->input('sorting'));

        $books = $books->paginate(50);
        $categories = Cache::remember('categories_list', 7200, function () {
            return Categories::select('id', 'name')->orderBy('name')->get();
        });

        $authors = Cache::remember('authors_list', 7200, function () {
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
