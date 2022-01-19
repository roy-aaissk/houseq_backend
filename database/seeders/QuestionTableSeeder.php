<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('questions')->insert([
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
            [
                'title' => '田中',
                'answer' => 'テストテストテストテストテストテストテストテストテストテストテストテストテスト',
                'tag' => 'テスト',
            ],
        ]);
    }
}
