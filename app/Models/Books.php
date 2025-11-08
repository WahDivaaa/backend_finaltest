<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
