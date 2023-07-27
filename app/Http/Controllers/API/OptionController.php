<?php

namespace App\Http\Controllers\API;

use App\Models\Option;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\API\OptionResource;

class OptionController extends BaseController
{

    public function index( Request $request )
    {

        if(!empty($request->name)) {
            return new OptionResource(Option::where('name', $request->name)->first());
        }

        $filters = json_decode($request->input('columnFilters'), true);
        $sorts = $request->input('sorts');
        $perPage = $request->get('perPage', 10);

        $options = Option::where(function ($query) use ($filters) {
            if (isset($filters['name']) && $filters['name'] != '') {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }
            if (isset($filters['autoload']) && $filters['autoload'] != '') {
                $query->where('autoload', '=', $filters['autoload']);
            }
        })
        ->when(isset($sorts), function ($query) use ($sorts) {
            foreach ($sorts as $sort) {
                $sort = json_decode($sort, true);
                if($sort['type'] === 'asc' || $sort['type'] === 'desc') {
                    $query->orderBy($sort['field'], $sort['type']);
                }
            }
        })
        ->when(!isset($sorts), function ($query) {
            $query->orderBy('id', 'desc');
        })
        ->paginate($perPage);
        return OptionResource::collection($options);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:options,name',
            'value' => 'required',
            'autoload' => 'boolean',
        ]);

        $option = Option::create([
            'name' => $request->name,
            'value' => json_decode($request->value),
            'autoload' => $request->autoload
        ]);
        return $this->sendResponse(new OptionResource($option), 'Option created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:options,name,'.$id,
            'value' => 'required|json',
            'autoload' => 'boolean',
        ]);

        $option = Option::findOrFail($id);

        $option->name =  $request->name;
        $option->value = json_decode($request->value);
        $option->autoload = $request->autoload;



        $option->save();

        return $this->sendResponse($option, 'Option updated successfully.');
    }

    public function destroy(Option $option)
    {
        $option->delete();
        return $this->sendResponse([], 'Option deleted successfully.');
    }
}
