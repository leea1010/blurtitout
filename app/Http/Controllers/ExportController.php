<?php

namespace App\Http\Controllers;

use App\Models\Therapist;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use League\Csv\Exception;

class ExportController extends Controller
{
    protected CsvExportService $csvExportService;

    public function __construct(CsvExportService $csvExportService)
    {
        $this->csvExportService = $csvExportService;
    }

    /**
     * Export all therapists to CSV
     *
     * @param Request $request
     * @return Response|JsonResponse
     * @throws Exception
     */
    public function exportTherapists(Request $request)
    {
        try {
            $query = Therapist::query();

            // Apply filters if provided
            if ($request->has('country') && $request->country) {
                $query->where('country', $request->country);
            }

            if ($request->has('state') && $request->state) {
                $query->where('state', $request->state);
            }

            if ($request->has('city') && $request->city) {
                $query->where('city', 'like', '%' . $request->city . '%');
            }

            if ($request->has('specialty') && $request->specialty) {
                $query->where('specialty', 'like', '%' . $request->specialty . '%');
            }

            if ($request->has('gender') && $request->gender) {
                $query->where('gender', $request->gender);
            }

            if ($request->has('online_offered') && $request->online_offered !== null) {
                $query->where('online_offered', $request->boolean('online_offered'));
            }

            // Get data
            $therapists = $query->get();

            // Custom filename if provided
            $filename = $request->get('filename');

            return $this->csvExportService->exportTherapists($therapists, $filename);
        } catch (\Exception $e) {
            Log::error('Export therapists error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export selected therapists to CSV
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function exportSelectedTherapists(Request $request): Response
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:therapists,id'
        ]);

        $therapists = Therapist::whereIn('id', $request->ids)->get();

        $filename = $request->get('filename', 'selected_therapists_' . date('Y-m-d_H-i-s') . '.csv');

        return $this->csvExportService->exportTherapists($therapists, $filename);
    }

    /**
     * Export all users to CSV
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function exportUsers(Request $request): Response
    {
        $query = User::query();

        // Apply filters if provided
        if ($request->has('email') && $request->email) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->has('name') && $request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('verified') && $request->verified !== null) {
            if ($request->boolean('verified')) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->has('created_from') && $request->created_from) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to') && $request->created_to) {
            $query->where('created_at', '<=', $request->created_to . ' 23:59:59');
        }

        // Get data
        $users = $query->get();

        // Custom filename if provided
        $filename = $request->get('filename');

        return $this->csvExportService->exportUsers($users, $filename);
    }

    /**
     * Export selected users to CSV
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function exportSelectedUsers(Request $request): Response
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id'
        ]);

        $users = User::whereIn('id', $request->ids)->get();

        $filename = $request->get('filename', 'selected_users_' . date('Y-m-d_H-i-s') . '.csv');

        return $this->csvExportService->exportUsers($users, $filename);
    }

    /**
     * Get export statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExportStats(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'therapists_count' => Therapist::count(),
            'users_count' => User::count(),
            'therapists_by_country' => Therapist::selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'users_verified_count' => User::whereNotNull('email_verified_at')->count(),
            'users_unverified_count' => User::whereNull('email_verified_at')->count(),
        ]);
    }
}
