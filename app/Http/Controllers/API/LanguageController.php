<?php

namespace App\Http\Controllers\API;

use App\Models\Language;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\LanguageResource;

class LanguageController extends Controller
{

    public function index()
    {
        return LanguageResource::collection(Language::all());
    }
}
