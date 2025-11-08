<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use App\Models\Books;
use App\Models\Ratings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authors = Authors::with('books:id,title,author_id')->get();
        return view('input_rating', compact('authors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authors = Authors::with('books:id,title,author_id')->get();
        return view('input_rating', compact('authors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'author_id' => 'required|exists:authors,id',
            'book_id'   => 'required|exists:books,id',
            'rate'      => 'required|integer|min:1|max:10',
        ]);

        $book = Books::with('author:id')->findOrFail($request->book_id);
        if ($book->author_id != $request->author_id) {
            return back()->withErrors(['book_id' => 'Invalid book-author combination.'])->withInput();
        }

        $recentRating = Ratings::where('book_id', $request->book_id)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->exists();

        if ($recentRating) {
            return back()->withErrors([
                'rate' => 'You must wait 24 hours before rating this book again.'
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                Ratings::create([
                    'book_id' => $request->book_id,
                    'rate' => $request->rate,
                ]);
            });

            return redirect()->route('books.index')->with('success', 'Rating successfully recorded!');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'general' => 'Failed to save rating. Please try again.'
            ])->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Ratings $ratings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ratings $ratings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ratings $ratings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ratings $ratings)
    {
        //
    }
}
