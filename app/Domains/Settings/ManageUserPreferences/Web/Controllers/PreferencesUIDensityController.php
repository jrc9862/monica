<?php

namespace App\Domains\Settings\ManageUserPreferences\Web\Controllers;

use App\Domains\Settings\ManageUserPreferences\Services\StoreUIDensityPreference;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferencesUIDensityController extends Controller
{
    public function store(Request $request)
    {
        $data = [
            'account_id' => Auth::user()->account_id,
            'author_id' => Auth::id(),
            'ui_density' => $request->input('ui_density'),
        ];

        $user = (new StoreUIDensityPreference)->execute($data);

        return response()->json([
            'data' => [
                'ui_density' => $user->ui_density,
            ],
        ], 200);
    }
}
