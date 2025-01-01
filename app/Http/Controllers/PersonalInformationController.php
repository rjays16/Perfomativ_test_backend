<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\MongoPersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PersonalInformationController extends Controller
{
    public function index(): JsonResponse
    {
        $sql_records = PersonalInformation::all();
        $mongo_records = MongoPersonalInformation::all();

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

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $validated['image'] = $imagePath;
        }

        try {
            // Store in MySQL
            $sql_record = PersonalInformation::create($validated);
            
            // Store in MongoDB
            $mongo_record = MongoPersonalInformation::create($validated);

            return response()->json([
                'message' => 'Record created successfully',
                'sql_data' => $sql_record,
                'mongo_data' => $mongo_record
            ], 201);
        } catch (\Exception $e) {
            // If image was uploaded but data creation failed, remove the image
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
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'date_of_birth' => 'sometimes|date',
            'state' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $validated['image'] = $imagePath;
        }

        try {
            $sql_record = PersonalInformation::findOrFail($id);
            $mongo_record = MongoPersonalInformation::where('_id', $id)->first();

            // Remove old image if new one is uploaded
            if (isset($imagePath) && $sql_record->image) {
                Storage::disk('public')->delete($sql_record->image);
            }

            $sql_record->update($validated);
            $mongo_record->update($validated);

            return response()->json([
                'message' => 'Record updated successfully',
                'sql_data' => $sql_record,
                'mongo_data' => $mongo_record
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
            $mongo_record = MongoPersonalInformation::where('_id', $id)->first();

            // Delete image if exists
            if ($sql_record->image) {
                Storage::disk('public')->delete($sql_record->image);
            }

            $sql_record->delete();
            $mongo_record->delete();

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