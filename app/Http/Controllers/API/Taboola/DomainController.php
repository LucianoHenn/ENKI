<?php

namespace App\Http\Controllers\API\Taboola;

use Illuminate\Http\Request;
use App\Models\Taboola\Domain;
use App\Http\Controllers\API\BaseController;
use App\Http\Resources\API\Taboola\DomainResource;
use Log;

class DomainController extends BaseController
{
    public function index(Request $request)
    {
        $filters = json_decode($request->input('columnFilters'), true);
        $sorts = $request->input('sorts');
        $perPage = $request->get('perPage', 10);

        $domains = Domain::with('countries')
            ->where(function ($query) use ($filters) {
                if (isset($filters['name']) && $filters['name'] != '') {
                    $query->where('name', 'like', '%' . $filters['name'] . '%');
                }
                if (isset($filters['template_url']) && $filters['template_url'] != '') {
                    $query->where('template_url', 'like', '%' . $filters['template_url'] . '%');
                }
                if (isset($filters['status']) && $filters['status'] != '') {
                    $query->where('status', '=', $filters['status']);
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
        return DomainResource::collection($domains);
    }

    /**
     * Store a newly created resource in database.
     *
     * @param Request $request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:taboola_domains|string|max:255',
            'url' => 'required|string|max:2048',
            'countries' => 'required|array',
            'status' => 'required|in:active,inactive',
        ]);

        $partnershipId = $request->partnership ? $request->partnership['id'] : null;

        $domain = Domain::create([
            'name' => strtolower($request->name),
            'domain' => $request->url,
            'partnership_id' => $partnershipId,
            'status' => $request->status,
        ]);

        $countryIds = $this->getCountryIds($request->countries);

        $domain->countries()->attach($countryIds);
        return $this->sendResponse(new DomainResource($domain), 'Domain created successfully.');
    }

    /**
     * Get data request to update the domain.
     *
     * @param Request $request The request.
     * @param int $id The domain id.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required|string|max:255|unique:taboola_domains,name," . $id,
            'url' => 'required|string|max:2048',
            'countries' => 'required|array',
            'status' => 'required|in:active,inactive',
        ]);

        $domain = Domain::find($id);
        $partnershipId = $request->partnership ? $request->partnership['id'] : null;

        $domain->update([
            'name' => strtolower($request->name),
            'domain' => $request->url,
            'partnership_id' => $partnershipId,
            'status' => $request->status,
        ]);
        $countryIds = $this->getCountryIds($request->countries);
        $domain->countries()->sync($countryIds);
        return $this->sendResponse(new DomainResource($domain), 'Domain updated successfully.');
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
        $x = Domain::findOrFail($id);

        return $x->delete();
    }
}
