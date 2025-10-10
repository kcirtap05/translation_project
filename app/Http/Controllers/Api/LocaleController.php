<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocaleRequest;
use App\Services\LocaleService;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $service;

    public function __construct(LocaleService $service)
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
    public function create(LocaleRequest $request)
    {
        return $this->service->create($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, LocaleRequest $request) 
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
