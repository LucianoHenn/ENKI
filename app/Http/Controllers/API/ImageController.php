<?php

namespace App\Http\Controllers\API;

use App\Models\Tag;
use App\Models\Image;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\API\ImageResource;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Services\Utils\App as App;
use App\Services\Utils\Image as ImageService;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Log;

class ImageController extends BaseController
{

    public function index(Request $request)
    {
        $sorts = $request->input('sorts');
        $maxWidth = $request->get('maxWidth');
        $minWidth = $request->get('minWidth');
        $maxHeight = $request->get('maxHeight');
        $minHeight = $request->get('minHeight');
        $tags = $request->get('tags');
        $numberOfKeywords = $request->get('numberOfKeywords');
        $perPage = $request->get('perPage', 10);
        $keywords = $request->get('keywords');
        $keywords = explode(',', $keywords);
        $keywords = array_map('trim', $keywords);
        $keywords = array_map('strtolower', $keywords);
        $keywords = array_unique($keywords);
        $keywords = array_filter($keywords, function ($value) {
            return !empty(trim($value));
        });

        $imageNames = $request->get('image_names');
        $imageNames = explode(',', $imageNames);
        $imageNames = array_map('trim', $imageNames);
        $imageNames = array_map('strtolower', $imageNames);
        $imageNames = array_unique($imageNames);
        $imageNames = array_filter($imageNames, function ($value) {
            return !empty(trim($value));
        });

        $images = Image::with('tags', 'keywords', 'keywords.country', 'keywords.language')
            ->when(isset($sorts), function ($query) use ($sorts) {
                foreach ($sorts as $sort) {
                    $sort = json_decode($sort, true);
                    if ($sort['field'] === 'keywords' && ($sort['type'] === 'asc' || $sort['type'] === 'desc')) {
                        $query->withCount('keywords')->orderBy('keywords_count', $sort['type']);
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
            ->when(isset($imageNames) && count($imageNames) > 0, function ($query) use ($imageNames) {
                foreach ($imageNames as $imageName) {
                    $query->orWhere('image_name', 'LIKE', "%{$imageName}%");
                }
            })
            ->when(isset($keywords) && count($keywords) > 0, function ($query) use ($keywords) {
                $query->whereHas('keywords', function ($query) use ($keywords) {
                    $query->whereIn('keyword', $keywords);
                });
            })
            ->when($numberOfKeywords !== null, function ($query) use ($numberOfKeywords) {
                $query->withCount('keywords')
                    ->having('keywords_count', $numberOfKeywords);
            })
            ->when(isset($minWidth), function ($query) use ($minWidth) {
                $query->where('width', '>=', $minWidth);
            })
            ->when(isset($maxWidth), function ($query) use ($maxWidth) {
                $query->where('width', '<=', $maxWidth);
            })
            ->when(isset($tags), function ($query) use ($tags) {
                $query->whereHas('tags', function ($query) use ($tags) {
                    $query->whereIn('value', $tags);
                });
            })
            ->when(isset($minHeight), function ($query) use ($minHeight) {
                $query->where('width', '>=', $minHeight);
            })
            ->when(isset($maxHeight), function ($query) use ($maxHeight) {
                $query->where('width', '<=', $maxHeight);
            })
            ->paginate($perPage);

        return ImageResource::collection($images);
    }

    public function store(Request $request)
    {

        $request->validate([
            "keywords" => "required",
            "countries" => "required|array",
            "languages" => "required",
            "category" => "required",
            "tags" => "required|array",
            "images.*" => "required|image|mimes:jpeg,png,jpg,gif,svg"
        ]);

        $images = $request->file('images');

        if (!is_array($images) || count($images) === 0) {
            return $this->sendError('No images were uploaded.');
        }

        foreach ($images as $image) {
            $alreadyExitsImage = $this->getAlreadyExitsImage($image);
            if ($alreadyExitsImage) {
                $this->createNewImageObject($request, $alreadyExitsImage);
            } else {
                $this->uploadNewImage($request, $image);
            }
        }

        return $this->sendResponse([], 'Images uploaded successfully.');
    }

    public function getAlreadyExitsImage($file)
    {
        $imagePerceptualHash = $this->getPerceptualHash($file->getRealPath());
        return Image::where('perceptual_hash', $imagePerceptualHash)->first();
    }

    public function createNewImageObject(Request $request, $image)
    {
        try {
            $this->attachKeywords($request, $image);
            $this->attachTags($request->input('tags'), $image);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function uploadNewImage(Request $request, $file)
    {
        $fileUpload = $this->uploadFile($file);
        $newUploadImage = $this->createImage($fileUpload);
        $this->attachKeywords($request, $newUploadImage);
        $this->attachTags($request->input('tags'), $newUploadImage);
    }

    public function uploadFile($file)
    {
        $fileContent = file_get_contents($file->getRealPath());
        $imageObject = new ImageService($fileContent, true);
        $imageObject = $imageObject->stripMeta();
        $hash = sha1($imageObject->getContent());
        $imageName = 'original.' . strtolower($imageObject->getExtension());
        $path = App::environment() . '/media/images/' . substr($hash, 0, 4) . '/' . $hash . '/' . $imageName;
        $size = filesize($file);



        Storage::disk('s3')->put('/' . $path, $fileContent);

        $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(360));

        return [
            'url' => $url,
            'image_name' =>  pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'path' => $path,
            'hash' => $hash,
            'name' => $imageName,
            "size" => $size,
            "mimetype" => $imageObject->getMimeType(),
            "width" => $imageObject->getWidth(),
            "height" => $imageObject->getHeight(),
        ];
    }

    public function attachKeywords($request, $image)
    {
        $keywords = $request->input('keywords');
        $keywords = preg_split('/\n|,/', $keywords);
        $keywords = array_map('trim', $keywords);
        $keywords = array_unique($keywords);
        $countryIds = $request->input('countries');
        $languageId = $request->input('languages');
        $categoryId = $request->input('category');



        if (count($keywords) > 0) {

            foreach ($keywords as $keyword) {
                if (empty($keyword) || $keyword === ' ') {
                    continue;
                }
                $alreadyExistKeyword = Keyword::where([['keyword', $keyword], ['language_id', $languageId]])->first();
                if (!$alreadyExistKeyword) {
                    $keyword = new Keyword(['keyword' => $keyword]);
                    $keyword->english_translation = $keyword->translate();
                } else
                    $keyword = $alreadyExistKeyword;
                foreach ($countryIds as $countryId) {
                    $keyword = Keyword::firstOrCreate([
                        'keyword' => $keyword->keyword,
                        'country_id' => $countryId,
                        'language_id' => $languageId,
                    ], [
                        'english_translation' => $keyword->english_translation
                    ]);

                    if ($categoryId) {
                        $keyword->category_id = $categoryId;
                    }

                    $keyword->save();

                    $imageAlreadyHasKeywordLinked = DB::table('keywordables')
                        ->where('keyword_id', $keyword->id)
                        ->where('keywordable_id', $image->id)
                        ->where('keywordable_type', 'App\Models\Image')
                        ->first();
                    if (!$imageAlreadyHasKeywordLinked)
                        $image->keywords()->syncWithoutDetaching($keyword->id);
                    $this->attachTags($request->input('tags'), $keyword);
                }
            }
        }
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
                ->where('taggable_type', 'App\Models\Image')
                ->where('tag_id', $tag->id)
                ->first();
            if (!$objectHasAlreadyTagLinked)
                $object->tags()->syncWithoutDetaching($tag->id);
        }
    }

    public function getPerceptualHash($imagePath)
    {
        $hasher = new ImageHash(new PerceptualHash());
        return $hasher->hash($imagePath);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "tags" => "sometimes|array",
        ]);

        $image = Image::find($id);
        $image->tags()->detach();
        $image->image_name = $request->image_name;
        $image->save();
        $this->attachTags($request->input('tags'), $image);
        return $this->sendResponse([], 'Image updated successfully.');
    }

    public function destroy($id)
    {
        $image = Image::find($id);
        $image->keywords()->detach();
        $image->tags()->detach();
        return $this->sendResponse([], 'Image ' . $id . ' now have no tags and keywords data.');
    }

    public function deleteKeyword($id, $keywordId)
    {
        $image = Image::find($id);
        $image->keywords()->detach($keywordId);
        return $this->sendResponse([], 'Keyword deleted successfully.');
    }

    public function addKeyword(Request $request, $id)
    {
        $request->validate([
            "keywords" => "required",
            "countries" => "required|array",
            "languages" => "required",
        ]);

        $countryIds = array_map(function ($country) {
            return $country['id'];
        }, $request->input('countries'));
        $languageId = $request->input('languages')['id'];

        $request->merge(['countries' => $countryIds]);
        $request->merge(['languages' => $languageId]);

        $image = Image::find($id);
        $this->attachKeywords($request, $image);
        return $this->sendResponse([], 'Keyword added successfully.');
    }

    public function createImage($fileUpload)
    {

        return Image::updateOrCreate([
            'hash' => $fileUpload['hash'],
            'perceptual_hash' => $this->getPerceptualHash($fileUpload['url'])->toHex(),
        ], [
            'image_name' => $fileUpload['image_name'],
            'url' => $fileUpload['path'],
            'size' => $fileUpload['size'],
            'width' => $fileUpload['width'],
            'height' => $fileUpload['height'],
            'mimetype' => $fileUpload['mimetype'],
            'info' => "{}",
        ]);
    }

    public function uploadImageWithKeywordId(Request $request, $ids = null)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $ids = $ids ? $ids : $request->input('ids');

        $images = $request->file('images');
        // try {
        $uploadedImages = [];
        foreach ($images as $image) {
            $alreadyExitsImage = $this->getAlreadyExitsImage($image);
            if ($alreadyExitsImage) {
                $newUploadImage =  $alreadyExitsImage;
            } else {
                $fileUpload = $this->uploadFile($image);
                $newUploadImage = $this->createImage($fileUpload);
            }
            if ($request->input('tags'))
                $this->attachTags($request->input('tags'), $newUploadImage);
            $newUploadImage->keywords()->syncWithoutDetaching($ids);
            $uploadedImages[] = $newUploadImage;
        }
        $res = ImageResource::collection($uploadedImages);
        return $this->sendResponse($res, 'Upload new images successfully.');
        // } catch (\Exception $e) {
        //     return $this->sendError("Upload new images failed. Please try again.");
        // }
    }

    public function uploadUnsplashImages($images, $imageName = 'unsplash-image')
    {
        $imageIds = [];
        foreach ($images as $imageUrl) {
            $imagePerceptualHash = $this->getPerceptualHash($imageUrl);
            $alreadyExitsImage =  Image::where('perceptual_hash', $imagePerceptualHash)->first();
            if ($alreadyExitsImage) {
                array_push($imageIds,  $alreadyExitsImage->id);
            } else {
                $info = pathinfo($imageUrl);
                $contents = file_get_contents($imageUrl);
                $file = '/tmp/' . $info['basename'];
                file_put_contents($file, $contents);
                $fileUpload = $this->uploadFile(new UploadedFile($file, $imageName));

                $newUploadImage = $this->createImage($fileUpload);
                array_push($imageIds, $newUploadImage->id);
            }
        }
        return  $imageIds;
    }
}
