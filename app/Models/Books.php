<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Books extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'author_id',
        'category_id',
        'isbn',
        'publisher',
        'year',
        'status',
        'location',
    ];

    public function author()
    {
        return $this->belongsTo(Authors::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function ratings()
    {
        return $this->HasMany(Ratings::class, 'book_id');
    }

    public function scopeWithBookStatistics(Builder $query, float $C, int $m): void
    {
        $sevenDaysAgo = now()->subDays(7)->toDateTimeString();

        $avgQuery = "(SELECT AVG(rate) FROM ratings WHERE ratings.book_id = books.id)";
        $countQuery = "(SELECT COUNT(*) FROM ratings WHERE ratings.book_id = books.id)";
        $recentAvgQuery = "(SELECT AVG(rate) FROM ratings WHERE ratings.book_id = books.id AND created_at >= '{$sevenDaysAgo}')";
        $historicalAvgQuery = "(SELECT AVG(rate) FROM ratings WHERE ratings.book_id = books.id AND created_at < '{$sevenDaysAgo}')";

        $query->select('books.*')
            ->selectRaw("COALESCE({$avgQuery}, 0) as ratings_avg_rate")
            ->selectRaw("COALESCE({$countQuery}, 0) as ratings_count")
            ->selectRaw(
                "((COALESCE({$countQuery}, 0) / (COALESCE({$countQuery}, 0) + ?)) * COALESCE({$avgQuery}, 0)) + ((? / (COALESCE({$countQuery}, 0) + ?)) * ?) AS weighted_rating",
                [$m, $m, $m, $C]
            )
            ->selectRaw(
                "CASE 
                    WHEN COALESCE({$recentAvgQuery}, 0) > COALESCE({$historicalAvgQuery}, 0) THEN 1 
                    WHEN COALESCE({$recentAvgQuery}, 0) < COALESCE({$historicalAvgQuery}, 0) THEN -1 
                    ELSE 0 
                END as trend_direction"
            );
    }

    public function scopeApplyFilters(Builder $query, Request $request): void
    {
        $keyword = $request->input('keyword');
        $selectedCategories = $request->input('category', []);
        $authorId = $request->input('author');
        $yearMin = $request->input('year_min');
        $yearMax = $request->input('year_max');
        $location = $request->input('location');
        $status = $request->input('status');

        if (!empty($keyword)) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('title', 'like', "%{$keyword}%")
                    ->orWhereHas('author', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                    ->orWhere('isbn', 'like', "%{$keyword}%")
                    ->orWhere('publisher', 'like', "%{$keyword}%");
            });
        }

        if (!empty($selectedCategories)) {
            $query->whereHas('category', fn($q) => $q->whereIn('category_id', $selectedCategories));
        }

        if (!empty($authorId)) {
            $query->where('author_id', $authorId);
        }

        if (!empty($location)) {
            $query->where('location', $location);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($yearMin) && !empty($yearMax)) {
            $query->havingBetween('year', [$yearMin, $yearMax]);
        } elseif (!empty($yearMin)) {
            $query->having('year', '>=', $yearMin);
        } elseif (!empty($yearMax)) {
            $query->having('year', '<=', $yearMax);
        }

        $ratingMin = $request->input('rating_min');
        $ratingMax = $request->input('rating_max');

        if (!empty($ratingMin) && !empty($ratingMax)) {
            $query->havingBetween('ratings_avg_rate', [$ratingMin, $ratingMax]);
        } elseif (!empty($ratingMin)) {
            $query->having('ratings_avg_rate', '>=', $ratingMin);
        } elseif (!empty($ratingMax)) {
            $query->having('ratings_avg_rate', '<=', $ratingMax);
        }
    }

    public function scopeApplySorting(Builder $query, ?string $sorting): void
    {
        switch ($sorting) {
            case 'total_votes':
                $query->orderByDesc('ratings_count');
                break;
            case 'recent_popularity':
                $query->withCount(['ratings' => fn($q) => $q->where('created_at', '>=', now()->subDays(30))])
                    ->orderByDesc('ratings_count');
                break;
            case 'alphabetical':
                $query->orderBy('title', 'asc');
                break;
            case 'weighted_average_rating':
            default:
                $query->orderByDesc('weighted_rating');
                break;
        }
    }
}
