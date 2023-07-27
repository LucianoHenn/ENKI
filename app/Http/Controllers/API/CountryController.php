<?php

namespace App\Http\Controllers\API;

use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\CountryResource;

class CountryController extends Controller
{

    public function index()
    {
        return CountryResource::collection(Country::all());
    }
}
