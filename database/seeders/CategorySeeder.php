<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Apparel',
            'Beauty and personal care',
            'Business and Industrial',
            'Electronics',
            'Family and Community',
            'Finance',
            'Health',
            'Home and garden',
            'Insurance',
            'Jobs and Education',
            'Occasions and Gifts',
            'Sports and Fitness',
            'Travel',
            'Vehicles'
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category, '-');
            Category::create([
                'name' => $category,
                'slug' => $slug
            ]);
        }
    }
}
