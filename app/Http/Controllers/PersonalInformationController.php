<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\MongoPersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PersonalInformationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // It;s like http://localhost:8000/api/personal-information?direction=desc
        // for fetching
        // const response = await fetch(`${API_URL}/personal-information?direction=${sortDirection}`);
        $sortDirection = $request->query('direction', 'asc');

        $sql_records = PersonalInformation::orderBy('first_name', $sortDirection)
            ->orderBy('last_name', $sortDirection)
            ->get();

    
        $mongo_records = MongoPersonalInformation::orderBy('first_name', $sortDirection)
            ->orderBy('last_name', $sortDirection)
            ->get();

        return response()->json([
            'sql_data' => $sql_records,
            'mongo_data' => $mongo_records
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $validated['image'] = $imagePath;
        }

        try {
            $sql_record = PersonalInformation::create($validated);
            $mongo_record = MongoPersonalInformation::create($validated);

            return response()->json([
                'message' => 'Record created successfully',
                'sql_data' => $sql_record,
                'mongo_data' => $mongo_record
            ], 201);
        } catch (\Exception $e) {
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            return response()->json([
                'message' => 'Error creating record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $sql_record = PersonalInformation::findOrFail($id);
        $mongo_record = MongoPersonalInformation::where('_id', $id)->first();

        return response()->json([
            'sql_data' => $sql_record,
            'mongo_data' => $mongo_record
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $sql_record = PersonalInformation::findOrFail($id);
        
            $mongo_records = MongoPersonalInformation::where('first_name', $sql_record->first_name)
                ->where('last_name', $sql_record->last_name)
                ->where('email', $sql_record->email)
                ->first();

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'date_of_birth' => 'required|date',
                'state' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('image')) {
                if ($sql_record->image) {
                    Storage::disk('public')->delete($sql_record->image);
                }
                $imagePath = $request->file('image')->store('profile_images', 'public');
                $validated['image'] = $imagePath;
            }

            $sql_record->update($validated);

            if ($mongo_records) {
                // If record exists, update it
                MongoPersonalInformation::where('_id', $mongo_records->_id)
                ->update($validated);
            } else {
                // If no record exists, create new one
                MongoPersonalInformation::create($validated);
            }

            return response()->json([
                'message' => 'Record updated successfully',
                'sql_data' => $sql_record,
                'mongo_data' => $mongo_records
            ]);
        } catch (\Exception $e) {
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        
            return response()->json([
                'message' => 'Error updating record',
                'error' => $e->getMessage()
            ], 500);
            }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $sql_record = PersonalInformation::findOrFail($id);
            
            // Find MongoDB record
            $mongo_record = MongoPersonalInformation::where('first_name', $sql_record->first_name)
                ->where('last_name', $sql_record->last_name)
                ->where('email', $sql_record->email)
                ->first();
    
            if ($sql_record->image) {
                Storage::disk('public')->delete($sql_record->image);
            }
    
            $sql_record->delete();
            
            if ($mongo_record) {
                $mongo_record->delete();
            }
    
            return response()->json([
                'message' => 'Record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting record',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}