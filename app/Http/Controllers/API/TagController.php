<?php

namespace App\Http\Controllers\API;

use App\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\TagResource;

class TagController extends Controller
{

    public function index()
    {
        return TagResource::collection(Tag::all());
    }
}
