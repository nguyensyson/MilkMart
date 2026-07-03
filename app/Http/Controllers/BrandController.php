<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Brand::orderBy('name')->get(['id', 'name', 'logo_url']),
        ]);
    }
}
