<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\APIs\FacebookApi\FacebookApi;


class AppController extends Controller
{
    public function index()
    {
        return view('app');
    }
    public function test()
    {
        // for test
    }
}
