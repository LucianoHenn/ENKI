<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\API\UserResource;
use Validator;

class AuthenticationController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken(str_replace(' ','_', config('app.name')))->plainTextToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function refresh(Request $request)
    {
        $user = Auth::user();
        $success['token'] =  $user->createToken(str_replace(' ','_', config('app.name')))->plainTextToken;
        $success['name'] =  $user->name;
        return $this->sendResponse($success, 'User refresh successfully.');
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
    }
}
