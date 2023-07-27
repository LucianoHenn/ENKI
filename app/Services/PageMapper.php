<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Country;
use App\Models\Facebook\Partnership;
use App\Models\Option;


class PageMapper
{


    public function linkPartnership($pageId,  $partnershipId, $marketIds)
    {
        if (!$partnershipId) {
            DB::table('facebook_pages_partnerships_markets')
                ->where('page_id', $pageId)
                ->delete();

            return 'Partnership unlinked succesfully';
        }

        DB::table('facebook_pages_partnerships_markets')
            ->where('page_id', $pageId)
            ->where('partnership_id', '!=', $partnershipId)
            ->orWhere(function ($query) use ($pageId, $partnershipId, $marketIds) {
                $query->where('page_id', $pageId)
                    ->where('partnership_id', $partnershipId)
                    ->whereNotIn('market_id', $marketIds);
            })
            ->delete();

        foreach ($marketIds as $marketId) {
            $data[] = [
                'market_id' => $marketId,
                'page_id' => $pageId,
                'partnership_id' => $partnershipId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('facebook_pages_partnerships_markets')->insertOrIgnore($data);

        return 'Partnership linked succesfully';
    }

    public function updateCategories($page_id, $categories)
    {

        DB::table('facebook_pages_categories')->where('page_id', $page_id)->whereNotIn('category_id', $categories)->delete();
        foreach ($categories as $category) {
            DB::table('facebook_pages_categories')->updateOrInsert(
                ['page_id' => $page_id, 'category_id' => $category],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function getPages()
    {
        $pages = Option::where('name', 'facebook_pages')->first();
        $pages = $pages->value;

        $categories = Category::all();
        $partnerships = Partnership::all();
        $countries = Country::all();



        $categories_array = $categories->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        })->toArray();

        $partnerships_array = $partnerships->mapWithKeys(function ($item) {

            return [$item->id => ['id' => $item->id, 'name' => $item->name]];
        })->toArray();

        $countries_array = $countries->mapWithKeys(function ($item) {
            return [$item->id => ['id' => $item->id, 'name' => $item->name, 'code' => $item->code]];
        })->toArray();


        $pages_categories = DB::table('facebook_pages_categories')->get();

        $pages_partnerships_markets = DB::table('facebook_pages_partnerships_markets')->get();


        $pages = array_map(function ($page) use ($pages_categories, $categories_array, $pages_partnerships_markets, $partnerships_array, $countries_array) {

            foreach ($pages_categories as $page_category) {

                if ($page_category->page_id == $page['id']) {
                    $page['category'][] = ['id' => $page_category->category_id, 'name' => $categories_array[$page_category->category_id]];
                }
            }

            foreach ($pages_partnerships_markets as $page_partnership_markets) {

                if ($page_partnership_markets->page_id == $page['id']) {
                    if (!array_key_exists('partnership', $page))
                        $page['partnership'] = $partnerships_array[$page_partnership_markets->partnership_id];
                    $page['partnership']['countries'][] = $countries_array[$page_partnership_markets->market_id];
                }
            }

            return $page;
        }, $pages);
        return $pages;
    }
}
