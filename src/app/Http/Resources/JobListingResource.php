<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'job_title' => $this->job_title,
            'job_description' => $this->job_description,
            'wage' => $this->wage,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'business' => [
                'id' => $this->business->id,
                'user_id' => $this->business->user_id,
                'company_name' => $this->business->company_name,
                'max_distance' => $this->business->max_distance,
                'ratings' => $this->business->ratings,
                'created_at' => $this->business->created_at,
                'updated_at' => $this->business->updated_at,
                'profile' => [
                    'id' => $this->business->profile->id,
                    'user_id' => $this->business->profile->user_id,
                    'profile_pic' => $this->business->profile->profile_pic,
                    'phone_no' => $this->business->profile->phone_no,
                    'country_abbr' => $this->business->profile->country_abbr,
                    'country_code' => $this->business->profile->country_code,
                    'address' => $this->business->profile->address,
                    'agency_code' => $this->business->profile->agency_code,
                    'about' => $this->business->profile->about,
                    'total_earnings' => $this->business->profile->total_earnings,
                    'pending_payment' => $this->business->profile->pending_payment,
                    'longitude' => $this->business->profile->longitude,
                    'latitude' => $this->business->profile->latitude,
                    'created_at' => $this->business->profile->created_at,
                    'updated_at' => $this->business->profile->updated_at,
                    'user' => [
                        'id' => $this->business->profile->user->id,
                        'fname' => $this->business->profile->user->fname,
                        'lname' => $this->business->profile->user->lname,
                        'email' => $this->business->profile->user->email,
                        'user_type_id' => $this->business->profile->user->user_type_id,
                        'email_verified_at' => $this->business->profile->user->email_verified_at,
                        'created_at' => $this->business->profile->user->created_at,
                        'updated_at' => $this->business->profile->user->updated_at,
                    ],
                ],
            ],
            'tasks' => $this->tasksData,
            'percatageCompleted' => $this->calculateCompletionPercentage()
        ];
    }

    private function calculateCompletionPercentage() {
        $tasks = $this->tasksData->toArray();
        // return $data;
        if (empty($tasks)) {
            return 0; 
        }
    
        $completedTasks = array_filter($tasks, function ($task) {
            return $task['isCompleted'] === true;
        });
    
        $percentage = (count($completedTasks) / count($tasks)) * 100;
    
        return round($percentage);
    }
}
