<?php

namespace App\Http\Controllers;

use App\Models\Therapist;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TherapistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Therapist::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('specialty', 'LIKE', "%{$search}%")
                    ->orWhere('general_expertise', 'LIKE', "%{$search}%")
                    ->orWhere('source', 'LIKE', "%{$search}%");
            });
        }

        // Sort by newest first
        $therapists = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('therapists.index', compact('therapists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('therapists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string',
            'city' => 'nullable|string',
            'experience_duration' => 'nullable|string',
            'avatar' => 'nullable|url',
            'specialty' => 'nullable|string',
            'general_expertise' => 'nullable|string',
        ]);

        // Convert specialty and general_expertise to JSON arrays
        if ($validated['specialty']) {
            $validated['specialty'] = json_encode(explode(',', $validated['specialty']));
        }
        if ($validated['general_expertise']) {
            $validated['general_expertise'] = json_encode(explode(',', $validated['general_expertise']));
        }

        Therapist::create($validated);

        return redirect()->route('therapists.index')->with('success', 'Therapist created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Therapist $therapist)
    {
        return view('therapists.show', compact('therapist'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Therapist $therapist)
    {
        return view('therapists.edit', compact('therapist'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Therapist $therapist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string',
            'city' => 'nullable|string',
            'experience_duration' => 'nullable|string',
            'avatar' => 'nullable|url',
            'specialty' => 'nullable|string',
            'general_expertise' => 'nullable|string',
        ]);

        // Convert specialty and general_expertise to JSON arrays
        if ($validated['specialty']) {
            $validated['specialty'] = json_encode(explode(',', $validated['specialty']));
        }
        if ($validated['general_expertise']) {
            $validated['general_expertise'] = json_encode(explode(',', $validated['general_expertise']));
        }

        $therapist->update($validated);

        return redirect()->route('therapists.index')->with('success', 'Therapist updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Therapist $therapist)
    {
        $therapist->delete();

        return redirect()->route('therapists.index')->with('success', 'Therapist deleted successfully!');
    }

    /**
     * Export therapists to CSV
     */
    public function export(Request $request)
    {
        $query = Therapist::query();

        // Apply same search filter as index
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('specialty', 'LIKE', "%{$search}%")
                    ->orWhere('general_expertise', 'LIKE', "%{$search}%")
                    ->orWhere('source', 'LIKE', "%{$search}%");
            });
        }

        $therapists = $query->orderBy('created_at', 'desc')->get();

        $filename = 'therapists_from_results_' . date('Y-m-d_H-i-s') . '.csv';
        if ($request->has('search')) {
            $filename = 'search_' . str_replace(' ', '_', $request->search) . '_' . date('Y-m-d_H-i-s') . '.csv';
        }

        // Use the new CsvExportService with proper format
        $csvService = new CsvExportService();
        return $csvService->exportTherapists($therapists, $filename);
    }
}
