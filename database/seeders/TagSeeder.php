<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $tags = [
            [ 'type' => 'source', 'value' => 'facebook'],
            [ 'type' => 'source', 'value' => 'google' ],
            [ 'type' => 'source', 'value' => 'native' ],
        ];

        foreach($tags as $tag) {
            Tag::create($tag);
        }
    }
}
