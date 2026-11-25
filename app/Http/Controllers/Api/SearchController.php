<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\OrganizationScope;
use App\Support\RegisterSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->toString());
        $groups = $query === ''
            ? []
            : RegisterSearch::lookup($query, OrganizationScope::ids());

        return response()->json([
            'q' => $query,
            'groups' => collect($groups)->map(fn (array $group) => [
                'title' => $group['definition']['title'],
                'code' => $group['definition']['code'] ?? null,
                'uri' => $group['definition']['uri'],
                'records' => $group['records']->map(fn ($record) => [
                    'id' => $record->getKey(),
                    'title' => RegisterSearch::title($record, $group['definition']),
                    'organization' => $record->organization?->amharicName(),
                ])->values(),
            ])->values(),
        ]);
    }
}
