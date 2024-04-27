<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Prefix the profile picture URL with the base URL
        $profilePicUrl = !empty($this->profile_pic) ? env('APP_URL') . 'api/images/' . $this->profile_pic : null;
        
        $activityStatus;
        switch ($this->activity_status) {
            case '1':
                $activityStatus = 'Active';
                break;
            case '2':
                $activityStatus = 'Suspended';
                break;
            case '3':
                $activityStatus = 'Inactive';
                break;
            
            default:
                $activityStatus = null;
                break;
        }

        return [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "phone" => $this->phone,
            "email" => $this->email,
            "inviteCode" => $this->invite_code,
            "profilePicture" => $profilePicUrl,
            "referral" => $this->referral,
            "activityStatus" => $this->activity_status,
            "activityStatusLabel" => $activityStatus,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
