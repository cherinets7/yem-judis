<?php

namespace App\Http\Controllers;

use App\Support\OrganizationScope;
use App\Support\RegisterSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->string('q')->toString());

        return view('search.index', [
            'q' => $query,
            'groups' => $query === ''
                ? []
                : RegisterSearch::lookup($query, OrganizationScope::ids()),
        ]);
    }
}
