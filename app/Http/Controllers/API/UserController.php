<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\API\UserResource;
use Illuminate\Support\Facades\Storage;
use App\Services\Utils\App as App;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $filters = json_decode($request->input('columnFilters'), true);
        $sorts = $request->input('sorts');
        $perPage = $request->get('perPage', 10);

        $users = User::with('roles')
            ->where(function ($query) use ($filters) {
                if (isset($filters['name']) && $filters['name'] != '') {
                    $query->where('name', 'like', '%' . $filters['name'] . '%');
                }
                if (isset($filters['email']) && $filters['email'] != '') {
                    $query->where('email', 'like', '%' . $filters['email'] . '%');
                }
                if (isset($filters['roles_list']) && $filters['roles_list'] != '') {
                    $query->whereHas('roles', function ($query) use ($filters) {
                        $query->where('name', '=', $filters['roles_list']);
                    });
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

        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'roles' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required:min:6',
            'c_password' => 'required|same:password',
        ]);

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user->assignRole($this->getRoles($request));
        return $this->sendResponse(new UserResource($user), 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'roles' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes:min:6',
            'c_password' => 'same:password',
        ]);

        $request->request->remove('created_at');
        $request->request->remove('updated_at');

        if (!empty($request->password)) {
            $input = $request->except(['created_at', 'updated_at']);
        } else {
            $input = $request->except(['created_at', 'updated_at', 'password', 'c_password']);
        }

        if (!empty($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }

        $user->update($input);
        $user->syncRoles($this->getRoles($request));
        return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }

    public function getRoles(Request $request)
    {
        return array_map(function ($role) {
            return $role['value'];
        }, $request->input('roles'));
    }

    public function destroy(User $user)
    {
        if ($user->id == auth()->user()->id) {
            return $this->sendError('You can not delete yourself.');
        }

        $user->delete();
        return $this->sendResponse([], 'User deleted successfully.');
    }

    public function updateAvatarProfile(Request $request)
    {
        $userId = auth()->user()->id;
        $user = User::find($userId);

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileContent = file_get_contents($file->getRealPath());
            $path = App::environment() . '/user-profile/' .  $user->id . '/avatar.png';
            Storage::disk('s3')->put('/' . $path, $fileContent);
        }
        return $this->sendResponse(new UserResource($user), 'Avatar profile updated successfully.');
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::find($id);
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);

        if (!\Hash::check($request->old_password, $user->password)) {
            return $this->sendError('Your current password does not matches with the password you provided. Please try again.');
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully.');
    }
}
