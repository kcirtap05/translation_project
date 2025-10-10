<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $service;

    public function __construct(TagService $service)
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
    public function create(TagRequest $request)
    {
        return $this->service->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, TagRequest $request) 
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
}
