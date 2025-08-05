<?php

namespace App\Http\Controllers;

use App\Models\PropertySaleHistory;
use App\Http\Resources\PropertySaleHistoryResource;
use Illuminate\Http\Request;

class PropertySaleHistoryController extends Controller
{
    /**
     * Lấy toàn bộ dữ liệu từ PropertySaleHistory.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $saleHistories = PropertySaleHistory::orderBy('created_at', 'DESC')->get();
            return PropertySaleHistoryResource::collection($saleHistories); // Use resource for formatting
        } catch (\Exception $e) {
            return response()->json(['error' => 'Can\'t get data'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'property_number' => 'required|string|max:50',
            'price_total' => 'nullable|integer',
            'registered_at' => 'nullable|date',
            // Add other fields as needed
        ]);

        $propertySaleHistory = PropertySaleHistory::create($validatedData);

        return response()->json($propertySaleHistory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertySaleHistory $propertySaleHistory)
    {
        return new PropertySaleHistoryResource($propertySaleHistory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertySaleHistory $propertySaleHistory)
    {
        $validatedData = $request->validate([
            'property_number' => 'required|string|max:50',
            'price_total' => 'nullable|integer',
            'registered_at' => 'nullable|date',
            // Add other fields as needed
        ]);

        $propertySaleHistory->update($validatedData);

        return response()->json($propertySaleHistory, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertySaleHistory $propertySaleHistory)
    {
        $propertySaleHistory->delete();

        return response()->json(null, 204);
    }

    public function getByPropertyNumber($propertyNumber)
    {
        $propertySaleHistory = PropertySaleHistory::findByPropertyNumber($propertyNumber);

        if (!$propertySaleHistory) {
            return response()->json([
                'message' => 'Property sale history not found'
            ], 404);
        }

        return response()->json($propertySaleHistory);
    }
}
