<?php

namespace App\Http\Controllers\API\Taboola;

use App\Http\Controllers\API\BaseController as Controller;
use App\Models\Taboola\Template;
use Illuminate\Http\Request;
use App\Http\Resources\API\Taboola\TemplateResource;
use App\Models\Category;
use App\Models\Language;


class TemplateController extends Controller
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
        $perPage = $request->get('perPage', 50);

        $templates = Template::with('category', 'language', 'countries')->where(function ($query) use ($filters) {
            if (isset($filters['country_id']) &&  $filters['country_id'] != '') {
                $query->whereHas('countries', function ($q) use ($filters) {
                    $q->where('countries.id', $filters['country_id']);
                });
            }
            if (isset($filters['category_id']) &&  $filters['category_id'] != '') {
                $query->where('category_id', '=', $filters['category_id']);
            }
            if (isset($filters['language_id']) && $filters['language_id'] != '') {
                $query->where('language_id', '=', $filters['language_id']);
            }
            if (isset($filters['description']) && $filters['description'] != '') {
                $query->where('description', 'like', '%' . $filters['description'] . '%');
            }
        })
            ->when(isset($sorts), function ($query) use ($sorts) {
                foreach ($sorts as $sort) {
                    $sort = json_decode($sort, true);
                    if ($sort['type'] === 'asc' || $sort['type'] === 'desc') {
                        $query->orderBy($sort['field'], $sort['type']);
                    }
                }
            })
            ->when(!isset($sorts), function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->paginate($perPage);
        return TemplateResource::collection($templates);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = $request->category ? Category::find($request->category['id']) : null;
        $language =  $request->language ? Language::find($request->language['id']) : null;
        $countries =  $request->language ? Language::find($request->language['id']) : null;

        $template = Template::create([
            'description' => $request->description,
            'template' => $request->template,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $countryIds = $request->countries ? $this->getCountryIds($request->countries) : null;

        if ($countryIds)
            $template->countries()->attach($countryIds);

        if ($category)
            $template->category()->associate($category);
        if ($language)
            $template->language()->associate($language);
        if ($language || $category)
            $template->save();



        return $this->sendResponse(new TemplateResource($template), 'Template created successfully.');
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = $request->category ? Category::find($request->category['id']) : null;
        $language =  $request->language ? Language::find($request->language['id']) : null;
        $x = Template::findOrFail($id);

        $x->description = $request->description;
        $x->template =    $request->template;

        $countryIds = $this->getCountryIds($request->countries);
        $x->countries()->sync($countryIds);

        if ($category)
            $x->category()->associate($category);
        else
            $x->category()->dissociate();
        if ($language)
            $x->language()->associate($language);
        else
            $x->language()->dissociate();

        return  $this->sendResponse($x->save(), 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $x = Template::findOrFail($id);

        return $x->delete();
    }

    /**
     * Get the country ids from the request countries array.
     *
     * @param array $countries The country array.
     * @return array The country ids.
     */
    public function getCountryIds($countries): array
    {
        return array_map(function ($country) {
            return $country['id'];
        }, $countries);
    }
}
