<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchByKeyRequest;
use App\Http\Requests\SearchByTagRequest;
use App\Http\Requests\TranslationRequest;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $service;

    public function __construct(TranslationService $service)
    {
        $this->service = $service;
    }

    public function grid($number_per_page = null) 
    {
        return $this->service->grid($number_per_page);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(TranslationRequest $request)
    {
        return $this->service->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, TranslationRequest $request) 
    {
        return $this->service->update($id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) 
    {
        return $this->service->delete($id);
    }

    public function searchByKey(SearchByKeyRequest $request)
    {

        $filters = [
            'key' => $request->input('q'),
            'per_page' => $request->input('per_page', 15),
        ];

        return $this->service->searchTranslations($filters);

    }

    public function searchByTags(SearchByTagRequest $request)
    {

        $filters = [
            'tags' => $request->input('tags'),
            'match' => $request->input('match', 'any'),
            'per_page' => $request->input('per_page', 15),
        ];

        return $this->service->searchByTags(
            $filters['tags'],
            $filters['match']
        );
    }

    public function advancedSearch(Request $request)
    {

        return $this->service->advancedSearch($request);
        
    }

    public function getX(Request $request)
    {
        return $this->service->getX($request);
        
    }
}
