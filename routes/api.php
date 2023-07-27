<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\JobberController;
use  App\Http\Controllers\API\OptionController;
use  App\Http\Controllers\API\Taboola\CampaignController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\KeywordController;
use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\Facebook\SiteController;
use App\Http\Controllers\API\Facebook\PartnershipController;
use App\Http\Controllers\API\Facebook\BidMultiplierController;
use App\Http\Controllers\API\Facebook\PageController;
use App\Http\Controllers\API\GoogleDrive\ImportImagesController;
use App\Http\Controllers\API\Google\KeywordToolsController;
use App\Http\Controllers\API\Unsplash\UnsplashController;
use App\Http\Controllers\API\Taboola\TaboolaController;


use App\Http\Controllers\API\ArcAssociationController;
use App\Http\Controllers\API\AuthenticationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('auth/login', [AuthenticationController::class, 'login'])->name('api.auth.login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('auth/me', [AuthenticationController::class, 'me'])->name('api.auth.me');
    Route::post('auth/logout', [AuthenticationController::class, 'logout'])->name('api.auth.logout');
    Route::post('auth/refresh', [AuthenticationController::class, 'refresh'])->name('api.auth.refresh');

    Route::get('countries', [CountryController::class, 'index'])->name('api.countries');
    Route::get('tags', [TagController::class, 'index'])->name('api.tags');
    Route::get('languages', [LanguageController::class, 'index'])->name('api.languages');
    Route::get('categories', [CategoryController::class, 'index'])->name('api.categories');
    Route::get('roles', [RoleController::class, 'index'])->name('api.roles');
    Route::post('users/update-avatar-profile', [UserController::class, 'updateAvatarProfile'])->name('api.users.update-profile');
    Route::put('users/change-password/{id}', [UserController::class, 'changePassword'])->name('api.users.change-password');
});

// ADMIN ROUTES
Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {

    Route::apiResources([
        'options'               => OptionController::class,
        'clients'               => ClientController::class,
    ]);

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index'])->name('api.users.index');
        Route::post('/', [UserController::class, 'store'])->name('api.users.store');
        Route::put('/{user}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');
    });
});

Route::group(['middleware' => ['auth:sanctum', 'role:normal|admin']], function () {

    Route::group(['prefix' => 'images'], function () {
        Route::get('/', [ImageController::class, 'index'])->name('api.images.index');
        Route::post('/', [ImageController::class, 'store'])->name('api.images.store');
        Route::post('/{image}', [ImageController::class, 'update'])->name('api.images.update');
        Route::delete('/{image}', [ImageController::class, 'destroy'])->name('api.images.destroy');
        Route::delete('/{image}/{keywordId}', [ImageController::class, 'deleteKeyword'])->name('api.images.delete-keyword');
        Route::post('/keyword/{image}', [ImageController::class, 'addKeyword'])->name('api.images.add-keyword');
    });

    Route::group(['prefix' => 'unsplash'], function () {
        Route::get('/search', [UnsplashController::class, 'searchPhotos'])->name('api.unsplash.search');
    });


    Route::group(['prefix' => 'google-drive'], function () {
        Route::get('/client-email', [ImportImagesController::class, 'getClientEmail'])->name('api.drive.get-email');
        Route::get('/checkFolder/{fileId}', [ImportImagesController::class, 'hasFolderPermissions']);
    });


    Route::group(['prefix' => 'taboola'], function () {
        Route::apiResources([
            'templates'               => App\Http\Controllers\API\Taboola\TemplateController::class,
            'domains'                 => App\Http\Controllers\API\Taboola\DomainController::class,
            'campaigns'                 => App\Http\Controllers\API\Taboola\CampaignController::class,
            'taboolaPartnerships'            => App\Http\Controllers\API\Taboola\PartnershipController::class,
        ]);
        Route::get('/', [TaboolaController::class, 'index'])->name('api.taboola.index');
        Route::get('/get-campaigns-data', [CampaignController::class, 'getCampaignsData'])->name('api.taboola.get-data');
        Route::get('/get-weekly-campaigns-data', [CampaignController::class, 'getWeeklyCampaignsData'])->name('api.taboola.get-data-weekly');
    });

    Route::group(['prefix' => 'keyword-tools'], function () {
        Route::get('/google/keywordIdeas', [KeywordToolsController::class, 'getKeywordIdeas'])->name('api.keyword-tools.keywordIdeas');
        Route::get('/google/historicalData', [KeywordToolsController::class, 'getHistoricalData'])->name('api.keyword-tools.historicalData');
        Route::get('/google/languages', [KeywordToolsController::class, 'getLanguages'])->name('api.keyword-tools.languages');
        Route::get('/google/countries', [KeywordToolsController::class, 'getCountries'])->name('api.keyword-tools.countries');
    });

    Route::get('/options', [OptionController::class, 'index'])->name('api.options.index');
    Route::get('/clients', [ClientController::class, 'index'])->name('api.client.index');

    Route::apiResources([
        'jobbers'               => JobberController::class,
    ]);


    //Route::resource('jobber', JobberController::class);
    //Route::post('jobber/store', [JobberController::class, 'store'])->name('api.jobber.store');

    Route::prefix('database')->group(function () {
        Route::get('keywords', [KeywordController::class, 'index'])->name('api.database.keywords.index');
        Route::get('keywords/search', [KeywordController::class, 'search'])->name('api.database.keywords.search');
        Route::post('keywords', [KeywordController::class, 'store'])->name('api.database.keywords.store');
        Route::post('keywords/storeWithUploadImage', [KeywordController::class, 'storeWithUploadImage'])->name('api.database.keywords.storeWithUploadImage');
        Route::post('keywords/storeDirectlyFromCampaignGenerator', [KeywordController::class, 'storeDirectlyFromCampaignGenerator'])->name('api.database.keywords.storeDirectlyFromCampaignGenerator');
        Route::delete('keywords/{id}', [KeywordController::class, 'delete'])->name('api.database.keywords.delete');
        Route::get('keywords/checkImages/{id}', [KeywordController::class, 'checkImagesRelated'])->name('api.database.keywords.checkImagesRelated');
        Route::post('keywords/import', [KeywordController::class, 'import'])->name('api.database.keywords.import');
        Route::get('keywords/images/{id}', [KeywordController::class, 'getAssociateImages'])->name('api.database.keywords.get-associate-image');
        Route::put('keywords/images/{id}', [KeywordController::class, 'removeAssociateImage'])->name('api.database.keywords.remove-associate-image');
        Route::post('keywords/upload-images', [ImageController::class, 'uploadImageWithKeywordId'])->name('api.images.upload-images-with-keywordid');
        Route::put('keywords/add-associate-images', [KeywordController::class, 'addAssociateImage'])->name('api.keywords.add-associate-images');
        Route::put('keywords/assign-category', [KeywordController::class, 'assignCategory'])->name('api.keywords.assign-category');
        Route::put('keywords/assign-bulk-category', [KeywordController::class, 'assignBulkCategory'])->name('api.keywords.assign-bulk-category');
        Route::put('keywords/{site}', [KeywordController::class, 'update'])->name('api.database.keywords.update');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
