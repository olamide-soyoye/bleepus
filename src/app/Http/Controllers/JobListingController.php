<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\JobApplicant;
use App\Models\JobListing;
use App\Models\Notification;
use App\Models\Professional;
use App\Models\Profile;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Constants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
class JobListingController extends Controller
{
    use HttpResponses;

    public function createJobOffer(Request $request){
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$business) {
            $business = Business::where('user_id', $user_id)->first();
            
            try {
                $validatedData = $request->validate([
                    'job_title' => 'required|string|max:255',
                    'job_description' => 'required|string|max:255',
                    'urgency' => 'required|string',
                    'wage' => 'required|numeric',
                ]);
            } catch (ValidationException $e) {
                return $this->error('Validation Error', $e->getMessage(), 422);
            }

            if (!$request->tasks || $request->tasks === '' || $request->tasks === null) {
                return $this->error('Error', 'Please add tasks for the job', 400);
            }
            
    
            $JobListing = JobListing::create([
                'job_title' => $validatedData['job_title'],
                'job_description' => $validatedData['job_description'],
                'wage' => $validatedData['wage'],
                "business_id" => $business->id,
                "urgency" => $request->urgency,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "qualifications" => json_encode($request->qualifications),
            ]);
            // "tasks" => json_encode($request->tasks)
            $JobListingId = $JobListing->id;
            foreach ($request->tasks as $taskData) {
                $task = Task::create([
                    'job_listing_id' => $JobListingId, 
                    'isCompleted' => false,
                    'title' => $taskData,
                ]);
                if (!$task) {
                    return $this->error('Error', "Unable to add $taskData", 400);
                }
            }
    
            if ($JobListing) {
                return $this->success([
                    'data' => $JobListing,
                ], 200);
            }
            return $this->error('Error', 'Could not post job', 400);
        }
        return $this->error('Error', 'Only Healthcare Providers are allowed to post jobs', 400);
    } 

    public function getPostedJobs(Request $request){
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$business) {
            $business = Business::where('user_id', $user_id)->first();
            $JobListing = JobListing::with("tasks")->where('business_id', $business->id)->get();

            // $tasks = Task::;
    
            if ($JobListing) {
                return $this->success([
                    'data' => $JobListing,
                ], 200);
            }
            return $this->error('Error', 'Could not get jobs you posted', 400);
        }
        return $this->error('Error', 'Only Healthcare Providers are allowed to view jobs posted by them', 400);
    }

    public function getJobsWithinProffessionalRange(Request $request){
        if (Auth::user()->user_type_id == Constants::$professional) {
            $latitude = $request->latitude ?? null;
            $longitude = $request->longitude ?? null;

            $loggedInUserId = Auth::id();
            $professional = Professional::where('user_id', $loggedInUserId)->first();

            if (!$professional) {
                return $this->error('Error', 'Unable to find professional', 400);
            }

            $maxDistance = $professional->max_distance ? $professional->max_distance : Constants::$defaultDistance ;

            if (!$latitude || !$longitude) {
                $latitude = (float)$professional['profile']->latitude;
                $longitude = (float)$professional['profile']->longitude; 
            }

            if ($request->has('distance')) {
                $distance = $request->input('distance');
                $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $distance";
            }else{
                $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $maxDistance";
            }
        
            $query = JobListing::join("businesses","job_listings.business_id","businesses.id")
            ->join('profiles', 'businesses.profile_id', 'profiles.id')
            ->join('users', 'businesses.user_id', '=', 'users.id') 
            ->select(
                'businesses.company_name',
                'businesses.max_distance',
                'businesses.ratings',
                'job_listings.*',
                'profiles.latitude',
                'profiles.longitude',
                'profiles.profile_pic',
                'profiles.phone_no',
                'profiles.country_abbr',
                'profiles.country_code',
                'profiles.address',
                'profiles.agency_code',
                'profiles.id as profileId',
                'users.fname',
                'users.lname',
                'users.id as userId',
                DB::raw("ROUND((ST_Distance_Sphere(point(profiles.longitude, profiles.latitude), point($longitude, $latitude)) / ". Constants::$mileConversion ."),2) AS distance_between")
            )
            // ->with("business.user","business.profile")
            ->whereRaw($rawQuery)
            ->where('users.user_type_id', Constants::$business);
            // ->with('profile');
        
            // Check if the request has a rating parameter
            if ($request->has('rating')) {
                $rating = $request->input('rating');
                $query->where('ratings', '>=', $rating);
            }

            // Check if the request has an availability parameter
            if ($request->has('availability')) {
                $availability = $request->input('availability');
                $query->where('status', '=', $availability);
            }
        
            $jobsAround = $query->get();
            // $jobsAround = $this->removeUnwantedKeys($jobsAround);
            
            return $this->success([
                'JobsAround' => $jobsAround->isEmpty() ? [] : $this->formatGetApplicantsList ($jobsAround),
            ], 200);
        }
        return $this->error('Error', 'Only Healthcare Professionals are allowed to view jobs around them them', 400);
    }
    private function formatGetApplicantsList($jobAround) {
        $data = [];
    
        foreach ($jobAround as $job) {
            $jobData = [
                'id' => $job->id,
                'company_name' => $job->company_name,
                'max_distance' => $job->max_distance,
                'ratings' => $job->ratings,
                'business_id' => $job->business_id,
                'job_title' => $job->job_title,
                'job_description' => $job->job_description,
                'address' => $job->address,
                'availability' => $job->availability,
                'job_type_id' => $job->job_type_id,
                'wage' => $job->wage,
                'duration' => $job->duration,
                'start_date' => $job->start_date,
                'end_date' => $job->end_date,
                'qualifications' => $job->qualifications,
                'urgency' => $job->urgency,
                'tasks' => $job->tasks,
                'status' => $job->status,
                'payment_status' => $job->payment_status, 
                'profiles' => [
                    'id' => $job->profileId,
                    'phone_no' => $job->phone_no,
                    'total_earnings' => $job->total_earnings,
                    'longitude' => $job->longitude,
                    'latitude' => $job->latitude,
                    'about' => $job->about,
                    'profile_pic' => $job->profile_pic,
                    'country_abbr' =>$job->country_abbr,
                    'country_code' =>$job->country_code,
                    'agency_code' =>$job->agency_code,
                ],
                'users' => [
                    'id' => $job->userId,
                    'fname' => $job->fname,
                    'lname' => $job->lname
                ],
                'distance_between' => $job->distance_between
            ];
        
            array_push($data, $jobData);
        }
    
        return $data;
    }

    public function getJobsById(Request $request){
        try {
            $job = JobListing::with("business", "business.profile","business.profile.user","tasks")
                ->findOrFail($request->jobId);

            return $this->success([
                'job' => $job,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Error','Job not found', 400);
        }
    }

    

}
