<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamApiController extends Controller
{
    /**
     * Get all designations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDesignations(Request $request): JsonResponse
    {
        $query = Designation::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $designations = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Designations retrieved successfully',
            'data' => $designations,
            'total' => $designations->count()
        ], 200);
    }
    
    /**
     * Get single designation by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getDesignationById($id): JsonResponse
    {
        $designation = Designation::with('teamMembers')->find($id);
        
        if (!$designation) {
            return response()->json([
                'success' => false,
                'message' => 'Designation not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Designation retrieved successfully',
            'data' => $designation
        ], 200);
    }
    
    /**
     * Get all team members
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeamMembers(Request $request): JsonResponse
    {
        $query = TeamMember::with('designation')
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
        
        // Filter by designation
        if ($request->has('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }
        
        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Limit results
        if ($request->has('limit')) {
            $query->limit($request->limit);
        }
        
        $members = $query->get();
        
        // Format response
        $members = $members->map(function($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'avatar' => $member->avatar ? asset('storage/' . $member->avatar) : null,
                'bio' => $member->bio,
                'skills' => is_array($member->skills) ? $member->skills : json_decode($member->skills, true),
                'experience_years' => $member->experience_years,
                'education' => $member->education,
                'achievements' => $member->achievements,
                'order' => $member->order,
                'is_active' => $member->is_active,
                'designation' => $member->designation ? [
                    'id' => $member->designation->id,
                    'name' => $member->designation->name,
                    'color' => $member->designation->color,
                    'icon' => $member->designation->icon
                ] : null,
                'social_links' => [
                    'facebook' => $member->facebook,
                    'twitter' => $member->twitter,
                    'linkedin' => $member->linkedin,
                    'instagram' => $member->instagram,
                    'github' => $member->github
                ],
                'created_at' => $member->created_at,
                'updated_at' => $member->updated_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Team members retrieved successfully',
            'data' => $members,
            'total' => $members->count()
        ], 200);
    }
    
    /**
     * Get single team member by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTeamMemberById($id): JsonResponse
    {
        $member = TeamMember::with('designation')->find($id);
        
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Team member not found'
            ], 404);
        }
        
        $data = [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
            'avatar' => $member->avatar ? asset('storage/' . $member->avatar) : null,
            'bio' => $member->bio,
            'skills' => is_array($member->skills) ? $member->skills : json_decode($member->skills, true),
            'experience_years' => $member->experience_years,
            'education' => $member->education,
            'achievements' => $member->achievements,
            'order' => $member->order,
            'is_active' => $member->is_active,
            'designation' => $member->designation ? [
                'id' => $member->designation->id,
                'name' => $member->designation->name,
                'color' => $member->designation->color,
                'icon' => $member->designation->icon
            ] : null,
            'social_links' => [
                'facebook' => $member->facebook,
                'twitter' => $member->twitter,
                'linkedin' => $member->linkedin,
                'instagram' => $member->instagram,
                'github' => $member->github
            ],
            'created_at' => $member->created_at,
            'updated_at' => $member->updated_at
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Team member retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get team members by designation
     *
     * @param int $designationId
     * @return JsonResponse
     */
    public function getMembersByDesignation($designationId): JsonResponse
    {
        $designation = Designation::find($designationId);
        
        if (!$designation) {
            return response()->json([
                'success' => false,
                'message' => 'Designation not found'
            ], 404);
        }
        
        $members = TeamMember::with('designation')
            ->where('designation_id', $designationId)
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        $members = $members->map(function($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'avatar' => $member->avatar ? asset('storage/' . $member->avatar) : null,
                'bio' => $member->bio,
                'skills' => is_array($member->skills) ? $member->skills : json_decode($member->skills, true),
                'experience_years' => $member->experience_years,
                'education' => $member->education,
                'achievements' => $member->achievements
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Team members retrieved successfully for designation: ' . $designation->name,
            'designation' => [
                'id' => $designation->id,
                'name' => $designation->name,
                'color' => $designation->color,
                'icon' => $designation->icon
            ],
            'data' => $members,
            'total' => $members->count()
        ], 200);
    }
}