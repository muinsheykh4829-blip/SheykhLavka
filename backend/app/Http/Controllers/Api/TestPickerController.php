<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestPickerController extends Controller
{
    public function checkAuth(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'authenticated_user_id' => $user ? $user->id : 'null',
            'user_data' => $user ? $user->toArray() : 'no user',
            'guard' => 'picker'
        ]);
    }
}
