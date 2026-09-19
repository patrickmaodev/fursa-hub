<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user();
        $user?->loadMissing('admin');

        return new UserResource($user);
    }
}
