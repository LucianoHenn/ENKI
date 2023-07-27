<?php

namespace App\Http\Controllers\API\Taboola;

use Illuminate\Http\Request;
use App\Models\Taboola\Partnership;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\API\Taboola\PartnershipResource;

class PartnershipController extends BaseController
{
    public function index(Request $request)
    {
        $filters = json_decode($request->input('columnFilters'), true);
        $sorts = $request->input('sorts');
        $perPage = $request->get('perPage', 10);

        $partnerships = Partnership::with('countries')
            ->where(function ($query) use ($filters) {
                if (isset($filters['name']) && $filters['name'] != '') {
                    $query->where('name', 'like', '%' . $filters['name'] . '%');
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
        return PartnershipResource::collection($partnerships);
    }

    /**
     * Store a newly created resource in database.
     *
     * @param Request $request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:taboola_partnerships|string|max:255',
            'countries' => 'required|array',
        ]);

        $partnership = Partnership::create([
            'name' => strtolower($request->name),
        ]);

        $countryIds = $this->getCountryIds($request->countries);

        $partnership->countries()->attach($countryIds);
        return $this->sendResponse(new PartnershipResource($partnership), 'Partnership created successfully.');
    }

    /**
     * Get data request to update the partnership.
     *
     * @param Request $request The request.
     * @param int $id The partnership id.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required|string|max:255|unique:facebook_partnerships,name," . $id,
            'countries' => 'required|array',
        ]);

        $partnership = Partnership::find($id);
        $partnership->update([
            'name' => strtolower($request->name),
            'status' => $request->status,
        ]);
        $countryIds = $this->getCountryIds($request->countries);
        $partnership->countries()->sync($countryIds);
        return $this->sendResponse(new PartnershipResource($partnership), 'Partnership updated successfully.');
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


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $x = Partnership::findOrFail($id);

        return $x->delete();
    }
}
