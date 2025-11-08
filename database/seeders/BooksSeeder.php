<?php

namespace Database\Seeders;

use App\Models\Books;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $faker = Faker::create();
        $now = Carbon::now();
        $batchSize = 2000;
        $books = [];

        // Ambil semua id author dan category ke memory (hemat query)
        $authors = DB::table('authors')->pluck('id')->toArray();
        $categories = DB::table('categories')->pluck('id')->toArray();

        $total = 100000;

        for ($i = 1; $i <= $total; $i++) {
            $books[] = [
                'title' => $faker->sentence(3),
                'author_id' => $authors[array_rand($authors)],
                'category_id' => $categories[array_rand($categories)],
                'isbn' => $faker->unique()->isbn13(),
                'publisher' => $faker->company(),
                'year' => $faker->numberBetween(1950, 2025),
                'status' => $faker->randomElement(['available', 'rented', 'reserved']),
                'location' => $faker->randomElement([
                    'Gianyar',
                    'Denpasar',
                    'Badung',
                    'Bangli',
                    'Buleleng',
                    'Jembrana',
                    'Tabanan'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($i % $batchSize === 0) {
                DB::table('books')->insert($books);
                $books = [];
            }
        }

        if (!empty($books)) {
            DB::table('books')->insert($books);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
