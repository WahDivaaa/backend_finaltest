<?php

namespace Database\Seeders;

use App\Models\Ratings;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // $faker = Faker::create();
        $now = Carbon::now();
        $batchSize = 2000;
        $ratings = [];

        // Ambil semua id buku ke memory
        $bookIds = DB::table('books')->pluck('id')->toArray();

        $total = 500000;

        for ($i = 1; $i <= $total; $i++) {
            $ratings[] = [
                'book_id' => $bookIds[array_rand($bookIds)],
                'rate' => rand(1, 10),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($i % $batchSize === 0) {
                DB::table('ratings')->insert($ratings);
                $ratings = [];
            }
        }

        if (!empty($ratings)) {
            DB::table('ratings')->insert($ratings);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
