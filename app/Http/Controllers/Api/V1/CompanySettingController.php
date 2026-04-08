<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateCompanySettingsRequest;
use App\Http\Resources\V1\CompanySettingResource;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = CompanySetting::all()->keyBy('key');

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully',
            'data' => $settings->map(fn ($s) => $s->value)->toArray(),
        ]);
    }

    public function update(UpdateCompanySettingsRequest $request): JsonResponse
    {
        foreach ($request->settings as $key => $value) {
            CompanySetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) || is_object($value) ? json_encode($value) : (string) $value]
            );
        }

        $settings = CompanySetting::all()->keyBy('key')->map(fn ($s) => $s->value)->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ]);
    }

    public function show(Request $request, string $key): JsonResponse
    {
        $setting = CompanySetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting retrieved successfully',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
            ],
        ]);
    }

    public function set(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required'],
        ]);

        CompanySetting::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($validated['value']) || is_object($validated['value'])
                ? json_encode($validated['value'])
                : (string) $validated['value']]
        );

        $setting = CompanySetting::where('key', $key)->first();

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => new CompanySettingResource($setting),
        ]);
    }
}
