<?php

namespace App\Http\Controllers\API;

use App\Models\Tag;
use App\Models\Keyword;
use App\Models\Image;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\API\KeywordResource;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\API\ImageResource;
use Illuminate\Support\Facades\DB;

use Log;

class KeywordController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = json_decode($request->input('columnFilters'), true);
        $sorts = $request->input('sorts');
        $perPage = $request->get('perPage', 10);

        $keywords = Keyword::with('tags', 'country', 'language', 'images', 'category')
            ->where(function ($query) use ($filters) {
                if (isset($filters['keyword']) && $filters['keyword'] != '') {
                    $query->where('keyword', 'like', '%' . $filters['keyword'] . '%');
                }
                if (isset($filters['english_translation'])) {
                    $query->where('english_translation', 'like', '%' . $filters['english_translation'] . '%');
                }
                if (isset($filters['country_id']) &&  $filters['country_id'] != '') {
                    $query->where('country_id', '=', $filters['country_id']);
                }
                if (isset($filters['language_id']) && $filters['language_id'] != '') {
                    $query->where('language_id', '=', $filters['language_id']);
                }
                if (isset($filters['category_id']) && $filters['category_id'] != '') {
                    if ($filters['category_id'] == 0)
                        $query->whereNull('category_id');
                    else
                        $query->where('category_id', '=', $filters['category_id']);
                }
            })
            ->when(isset($sorts), function ($query) use ($sorts) {
                foreach ($sorts as $sort) {
                    $sort = json_decode($sort, true);
                    if ($sort['field'] === 'images' && ($sort['type'] === 'asc' || $sort['type'] === 'desc')) {
                        $query->withCount('images')->orderBy('images_count', $sort['type']);
                    } else {
                        if ($sort['type'] === 'asc' || $sort['type'] === 'desc') {
                            $query->orderBy($sort['field'], $sort['type']);
                        }
                    }
                }
            })
            ->when(!isset($sorts), function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->paginate($perPage);
        return KeywordResource::collection($keywords);
    }


    public function search(Request $request)
    {
        $country_id =   (int)$request->country_id;
        $language_id =  (int)$request->language_id;
        $category_id = (int)$request->category_id;
        $keyword =   $request->keyword ?? '';
        $tag =   $request->tag ?? '';
        $amount = (int)$request->amount ?? '';
        $query = Keyword::query();

        //  where tag value is $tag
        if ($tag) {
            if (is_array($tag)) {
                $query->whereHas('tags', function ($query) use ($tag) {
                    $query->whereIn('value', $tag);
                });
            } else {
                $query->whereHas('tags', function ($query) use ($tag) {
                    $query->where('value', $tag);
                });
            }
        }
        if ($country_id) {
            $query->where('country_id', $country_id);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($language_id) {
            $query->where('language_id', $language_id);
        }
        if (is_array($keyword)) {
            $query->whereIn('keyword', $keyword);
            $keywords = $query->get();
        } else {
            if ($keyword) {
                $query->where('keyword', 'like', '%' . $keyword . '%');
            }
            // take 10 records
            if ($amount)
                $keywords = $query->take($amount)->get();
            else
                $keywords = $query->take(10)->get();
        }

        return KeywordResource::collection($keywords);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "keywords" => "required",
            "tags" => "required|array",
            "countries" => "required|array",
            "language" => "required",
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $keywords = $request->input('keywords');
        $keywords = explode(',', $keywords);
        $keywords = array_map('trim', $keywords);
        $keywords = array_map('strtolower', $keywords);
        $keywords = array_unique($keywords);
        $countries = $request->input('countries');
        $language = $request->input('language');
        $images = $request->file('images') ?? null;
        $keyword_ids = [];
        $category = $request->input('category') ?  Category::find($request->input('category')['id']) : null;


        if (count($keywords) > 0) {
            foreach ($keywords as $word) {

                // Here we are just creating this keyword in order to get the translation only once, because we get charged for using the service

                foreach ($countries as $country) {

                    $keyword =  Keyword::where([
                        'keyword' => $word,
                        'country_id' => $country['id'],
                        'language_id' => $language['id']
                    ])->first();

                    if (!$keyword) {
                        $keyword = new Keyword([
                            'keyword' => $word,
                            'country_id' => $country['id'],
                            'language_id' => $language['id']
                        ]);
                        $keyword->english_translation = $keyword->translate();
                    }

                    if ($category) {
                        $keyword->category()->associate($category);
                    }
                    $keyword->save();
                    $this->attachTags($request->input('tags'), $keyword);

                    array_push($keyword_ids, $keyword->id);
                }
            }
        }

        if ($images)
            app('App\Http\Controllers\API\ImageController')->uploadImageWithKeywordId($request, $keyword_ids);

        elseif ($request->input('image_urls') && $request->input('image_ids'))
            $this->addAssociateImage($request, $keyword_ids);

        return $this->sendResponse(new KeywordResource($keyword), count($keywords) > 1  ? 'Keywords created successfully.' : 'Keyword created successfully.');
    }


    public function storeWithUploadImage(Request $request)
    {
        $request->validate([
            "keywords" => "required",
            "tags" => "required|array",
            "countryIds" => "required|array",
            "languageId" => "required",
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $keywords = $request->input('keywords');
        $keywords = explode(',', $keywords);
        $keywords = array_map('trim', $keywords);
        $keywords = array_map('strtolower', $keywords);
        $keywords = array_unique($keywords);
        $countries = $request->input('countryIds');
        $language = $request->input('languageId');
        $keyword_ids = [];
        $category = $request->input('category') ?  Category::find($request->input('category')) : null;

        if (count($keywords) > 0) {
            foreach ($keywords as $word) {

                foreach ($countries as $country) {
                    $keyword =  Keyword::where([
                        'keyword' => $word,
                        'country_id' => $country,
                        'language_id' => $language
                    ])->first();

                    if (!$keyword) {
                        $keyword = new Keyword([
                            'keyword' => $word,
                            'country_id' => $country,
                            'language_id' => $language
                        ]);
                        $keyword->english_translation = $keyword->translate();
                    }

                    if ($category) {
                        $keyword->category()->associate($category);
                    }
                    $keyword->save();
                    $this->attachTags($request->input('tags'), $keyword);

                    array_push($keyword_ids, $keyword->id);
                }
            }
        }

        app('App\Http\Controllers\API\ImageController')->uploadImageWithKeywordId($request, $keyword_ids);

        return $this->sendResponse(new KeywordResource($keyword), count($keywords) > 1  ? 'Keywords created successfully.' : 'Keyword created successfully.');
    }

    public function attachTags($tags, $object)
    {
        $tags = array_unique($tags);
        foreach ($tags as $tag) {
            $tag = Tag::firstOrCreate([
                'value' => $tag,
            ], [
                'type' => 'source',
            ]);
            $objectHasAlreadyTagLinked = DB::table('taggables')
                ->where('taggable_id', $object->id)
                ->where('taggable_type', 'App\Models\Keyword')
                ->where('tag_id', $tag->id)
                ->first();
            if (!$objectHasAlreadyTagLinked)
                $object->tags()->syncWithoutDetaching($tag->id);
        }
    }

    public function getAssociateImages(Request $request, $id)
    {
        $keyword = Keyword::find($id);
        $limit = $request->limit ?? '';
        $res['total_images'] =  $keyword->images->count();

        if ($limit) {
            $images = $keyword->images->take($limit);
        } else {
            $images = $keyword->images;
        }

        $res['images'] = ImageResource::collection($images);

        return response()->json($res);
    }

    public function removeAssociateImage(Request $request, $id)
    {
        $keyword = Keyword::find($id);
        $keyword->images()->detach($request->input('image_ids'));
        return $this->sendResponse([], 'Remove associate images successfully.');
    }

    public function addAssociateImage(Request $request, $ids = null)
    {

        if ($request->input('fromUnsplash')) {
            $imageIds =  app('App\Http\Controllers\API\ImageController')->uploadUnsplashImages($request->input('image_urls'), $request->unsplashName);
        } else
            $imageIds = $request->input('image_ids');

        $ids = $ids ? $ids : $request->input('ids');
        if (is_int($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            $keyword = Keyword::find($id);
            $keyword->images()->syncWithoutDetaching($imageIds);
        }
        // image_ids is array from keyword images
        $images =  Image::whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            if ($request->input('tags'))
                app('App\Http\Controllers\API\ImageController')->attachTags($request->input('tags'), $image);
        }

        $res = ImageResource::collection($images);

        return $this->sendResponse($res, 'Add associate images successfully.');
    }

    public function assignCategory(Request $request)
    {
        $ids = $request->input('ids');
        if (is_int($ids)) {
            $ids = [$ids];
        }



        foreach ($ids as $id) {
            $keyword = Keyword::find($id);
            $keyword->category_id = $request->input('category')[0]['id'];
            $keyword->save();
        }

        return $this->sendResponse(true, 'Category assigned succesfully');
    }

    public function assignBulkCategory(Request $request)
    {
        $keyword_categories_array = $request->input('keywordCategoriesObj');

        foreach ($keyword_categories_array as $key => $value) {

            $category = Category::where('name', $value)->first();

            if ($category)
                Keyword::where('keyword', 'like', $key)
                    ->update(['category_id' => $category->id]);
        }

        return $this->sendResponse(true, 'Categories assigned succesfully');
    }

    public function delete($id)
    {
        $keyword = Keyword::findOrFail($id);

        DB::table('keywordables')->where('keyword_id', $id)->delete();

        $keyword->delete();

        return $this->sendResponse(true, 'Keyword deleted succesfully');
    }


    // This endpoint is to check wether an Image still has another keyword related to it in order to delete the keyword
    // and don´t leave the image by itself.
    public function checkImagesRelated($id)
    {
        $keyword = Keyword::with('images')->findOrFail($id);

        // Get the ids of the images related to the keyword
        $relatedImageIds = $keyword->images->pluck('id')->toArray();

        // Images that only have that specific keyword related
        $uniqueImages = [];

        foreach ($relatedImageIds as $imageId) {
            // Get the count of images related to other keywords
            $count = DB::table('keywordables')
                ->where('keywordable_type', 'App\Models\Image')
                ->where('keyword_id', '!=', $id)
                ->where('keywordable_id', $imageId)
                ->count();

            if ($count === 0)
                array_push($uniqueImages,  Image::find($imageId));
        }

        return $this->sendResponse(ImageResource::collection($uniqueImages), 'Retrieved images that would be left without a keyword');
    }

    public function storeDirectlyFromCampaignGenerator(Request $request)
    {


        $request->validate([
            "keywords" => "required",
            "country" => "required",
            "language" => "required",
        ]);

        $keywords = $request->input('keywords');
        $countryId = $request->input('country');
        $languageId = $request->input('language');
        $keywordResources = [];


        foreach ($keywords as $word) {

            $category = Category::find($word['category']['id']);

            // Here we are just creating this keyword in order to get the translation only once, because we get charged for using the service
            $keyword =  Keyword::where([
                'keyword' => $word['keyword'],
                'country_id' => $countryId,
                'language_id' => $languageId
            ])->first();

            if (!$keyword) {
                $keyword = new Keyword([
                    'keyword' => $word['keyword'],
                    'country_id' => $countryId,
                    'language_id' => $languageId
                ]);
                $keyword->english_translation = $keyword->translate();
            }

            if ($category) {
                $keyword->category()->associate($category);
            }

            $keyword->save();

            $imageIds = array_map(function ($innerArray) {
                return $innerArray['id'];
            }, $word['images']);

            $keyword->images()->syncWithoutDetaching($imageIds);


            $keyword->load('language');
            $keyword->load('country');

            $images =  Image::whereIn('id', $imageIds)->get();
            $keyword->images = ImageResource::collection($images);

            array_push($keywordResources, $keyword);
        }

        return $this->sendResponse($keywordResources, count($keywords) > 1  ? 'Keywords stored successfully.' : 'Keyword stored successfully.');
    }
}
