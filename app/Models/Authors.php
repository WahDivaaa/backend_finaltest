<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Authors extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function books() {
        return $this->hasMany(Books::class, 'author_id');
    }

    public function ratings() {
        return $this->hasManyThrough(Ratings::class, Books::class,'author_id','book_id');
    }

    public function getBestRatedBookAttribute()
    {
        $cacheKey = "author:{$this->id}:best_book";

        return Cache::remember($cacheKey, 3600, function () {
            return $this->books()
                ->withAvg('ratings', 'rate') 
                ->orderByDesc('ratings_avg_rate')
                ->first();
        });
    }
    
    public function getWorstRatedBookAttribute()
    {
        $cacheKey = "author:{$this->id}:worst_book";
        return Cache::remember($cacheKey, 3600, function () {
            return $this->books()
                ->withAvg('ratings', 'rate')
                ->orderBy('ratings_avg_rate')
                ->first();
        });
    }
}
