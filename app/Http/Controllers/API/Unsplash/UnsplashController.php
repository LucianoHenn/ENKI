<?php

namespace App\Http\Controllers\API\Unsplash;

use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Http\Request;
use App\Services\Unsplash;


class UnsplashController extends Controller
{
    public function searchPhotos(Request $request)
    {
        $service = new Unsplash();

        $photos = $service->searchPhotos($request->all());

        return $this->sendResponse($photos, 'Photos retrieved succesfully');
    }
}
