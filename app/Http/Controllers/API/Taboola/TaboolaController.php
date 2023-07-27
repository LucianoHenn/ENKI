<?php

namespace App\Http\Controllers\API\Taboola;

use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Http\Request;
use App\Services\APIs\TaboolaApi;


class TaboolaController extends Controller
{
    public function index(Request $request)
    {
        $taboolaService = new TaboolaApi();
        $accountId = 'bidberrysrl-sy1-localservices-sc';

        $campaign =  $taboolaService->get(
            '/api/1.0/' . $accountId . '/campaigns/24435518'
        );
        dd($campaign);
        return $this->sendResponse($campaign, 'message');
    }
}
