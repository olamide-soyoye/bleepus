<?php

namespace App\Http\Controllers;

use App\Models\JobApplicant;
use App\Models\JobListing;
use App\Models\Notification;
use App\Models\Professional;
use App\Models\Profile;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Constants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use HttpResponses;

    // public function getProfessionalsOnJob() {
    //     $user_id = Auth::id();
    //     if (Auth::user()->user_type_id == Constants::$business) {
    //         $business = Profile::with("user","business")->where('user_id', $user_id)->first();
    //         $businessId = $business["business"]["id"];
            
    //         $jobApplicants = JobApplicant::join("job_listings", "job_applicants.job_listing_id","job_listings.id")
    //         ->join("businesses","job_listings.business_id","businesses.id")
    //         ->join("profiles","businesses.profile_id","profiles.id")
    //         ->join("professionals","job_applicants.professional_id","professionals.id")
    //         ->join("users","professionals.user_id","users.id")
    //         ->leftJoin("tasks", "job_listings.id", "=", "tasks.job_listing_id")
    //         ->select(
    //             'professionals.id as professionalId',
    //             'professionals.user_id',
    //             'professionals.profile_id',
    //             'professionals.max_distance',
    //             'professionals.profession_title',
    //             'professionals.skills',
    //             'professionals.certifications',
    //             'professionals.years_of_experience',
    //             'professionals.wage',
    //             'professionals.ratings',
    //             'professionals.specialities',

    //             'job_listings.id as jobId',
    //             'job_listings.job_title',
    //             'job_listings.job_description',
    //             'job_listings.wage',
    //             'job_listings.business_id',
    //             'job_listings.availability',
    //             'job_listings.job_type_id',
    //             'job_listings.duration',
    //             'job_listings.start_date',
    //             'job_listings.end_date',
    //             'job_listings.qualifications',
    //             'job_listings.urgency',
    //             'job_listings.tasks',
    //             'job_listings.payment_status',  

    //             "profiles.phone_no","profiles.total_earnings","profiles.longitude", "profiles.latitude","profiles.id as profileId",
    //             "profiles.about","users.fname","users.lname", "users.id as UserId")
    //             ->where("job_listings.business_id", $businessId)
    //             ->where("job_applicants.status", "Hired")
    //             ->where("job_applicants.isDone", false)
    //             ->orderBy('job_listings.job_title')
    //             ->orderBy('job_listings.id')
    //             ->get();

    //         // return $jobApplicants;
    //         $pendingCount = JobListing::where('business_id', $businessId)->where('status', 'Published')->count();

    //         $totalCount = JobListing::where('business_id', $businessId)->count();

    //         $hiredCount = JobListing::where('business_id', $businessId)->where('status', 'Occupied')->count();


    //         // Prepare the response
    //         $response = [
    //             'pending' => $pendingCount,
    //             'total' => $totalCount,
    //             'hired' => $hiredCount,
    //         ];
    //         // return $response;
    //         if ($jobApplicants) {
    //             $data = [
    //                 "analytics" => $response,
    //                 "workingProfessional"=>$jobApplicants->isEmpty() ? [] : $this->formatGetApplicantsList($jobApplicants)
    //             ];
    //             return $this->success([
    //                 $data,
    //             ], 200);
    //         }
    //     } 
    //     return $this->error('Error', 'Only Healthcare Providers can view jobs applicants', 400);
    // }
    public function getProfessionalsOnJob() {
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$business) {
            $business = Profile::with("user","business")->where('user_id', $user_id)->first();
            $businessId = $business["business"]["id"];
            // return $businessId;
            $jobApplicants = JobApplicant::with(
                "jobListing",
                "jobListing.business",
                "jobListing.tasks",
                "professional",
                "professional.profile",
                "professional.profile.user"
                )
                ->whereHas('jobListing', function($query) use ($businessId) {
                    $query->where('business_id', $businessId);
                })
                ->where("job_applicants.status", "Hired")
                ->where("job_applicants.isDone", false)
                ->get();
            
            $pendingCount = JobListing::where('business_id', $businessId)->where('status', 'Published')->count();

            $totalCount = JobListing::where('business_id', $businessId)->count();

            $hiredCount = JobListing::where('business_id', $businessId)->where('status', 'Occupied')->count();


            // Prepare the response
            $response = [
                'pending' => $pendingCount,
                'total' => $totalCount,
                'hired' => $hiredCount,
            ];
            // return $response;
            if ($jobApplicants) {
                $data = [
                    "analytics" => $response,
                    "workingProfessional"=>$jobApplicants->isEmpty() ? [] :$jobApplicants 
                ];
                // return $this->success([
                //     $data,
                // ], 200);
                return response()->json([
                    'message'=>"Request was successful",
                    'data'=>$data,
                ], 200);
            }
        } 
        return $this->error('Error', 'Only Healthcare Providers can view jobs applicants', 400);
    }

    // private function formatGetApplicantsList ($jobApplicants) {
    //     $data = [
    //         'job_listings' => [
    //             'id' => $jobApplicants[0]->jobId,
    //             'job_title' => $jobApplicants[0]->job_title,
    //             'job_description' => $jobApplicants[0]->job_description,
    //             'wage' => $jobApplicants[0]->wage,
    //             'availability' => $jobApplicants[0]->availability,
    //             'duration' => $jobApplicants[0]->duration,
    //             'start_date' => $jobApplicants[0]->start_date,
    //             'end_date' => $jobApplicants[0]->end_date,
    //             'qualifications' => $jobApplicants[0]->qualifications,
    //             'urgency' => $jobApplicants[0]->urgency,
    //             'tasks' => $jobApplicants[0]->tasks,
    //             'payment_status' => $jobApplicants[0]->payment_status,
    //         ],
    //         'professionals' => [
    //             'id' => $jobApplicants[0]->professionalId,
    //             'max_distance' => $jobApplicants[0]->max_distance,
    //             'total_earnings' => $jobApplicants[0]->total_earnings,
    //             'skills' => $jobApplicants[0]->skills,
    //             'certifications' => $jobApplicants[0]->certifications,
    //             'years_of_experience' => $jobApplicants[0]->years_of_experience,
    //             'wage' => $jobApplicants[0]->wage,
    //             'ratings' => $jobApplicants[0]->ratings,
    //             'specialities' => $jobApplicants[0]->specialities,
    //         ],
    //         'profiles' => [
    //             'phone_no' => $jobApplicants[0]->phone_no,
    //             'total_earnings' => $jobApplicants[0]->total_earnings,
    //             'longitude' => $jobApplicants[0]->longitude,
    //             'latitude' => $jobApplicants[0]->latitude,
    //             'about' => $jobApplicants[0]->about,
    //         ],
    //         'users' => [
    //             'fname' => $jobApplicants[0]->fname,
    //             'lname' => $jobApplicants[0]->lname,
    //             'id' => $jobApplicants[0]->UserId
    //         ],
    //         // 'tasks' => [
    //         //     'title' => $jobApplicants[0]->title,
    //         //     'isCompleted' => $jobApplicants[0]->isCompleted,
    //         // ],
    //     ];
    //     return $data;
    // }

    private function formatGetApplicantsList($jobApplicants) {
        $data = [];
    
        foreach ($jobApplicants as $applicant) {
            $jobData = [
                'id' => $applicant->jobId,
                'job_title' => $applicant->job_title,
                'job_description' => $applicant->job_description,
                'wage' => $applicant->wage,
                'availability' => $applicant->availability,
                'duration' => $applicant->duration,
                'start_date' => $applicant->start_date,
                'end_date' => $applicant->end_date,
                'qualifications' => $applicant->qualifications,
                'urgency' => $applicant->urgency,
                'payment_status' => $applicant->payment_status,
                'professionals' => [
                    'id' => $applicant->professionalId,
                    'max_distance' => $applicant->max_distance,
                    'total_earnings' => $applicant->total_earnings,
                    'skills' => $applicant->skills,
                    'certifications' => $applicant->certifications,
                    'years_of_experience' => $applicant->years_of_experience,
                    'wage' => $applicant->wage,
                    'ratings' => $applicant->ratings,
                    'specialities' => $applicant->specialities,
                ],
                'profiles' => [
                    'id' => $applicant->profileId,
                    'phone_no' => $applicant->phone_no,
                    'total_earnings' => $applicant->total_earnings,
                    'longitude' => $applicant->longitude,
                    'latitude' => $applicant->latitude,
                    'about' => $applicant->about,
                ],
                'users' => [
                    'id' => $applicant->UserId,
                    'fname' => $applicant->fname,
                    'lname' => $applicant->lname
                ],
            ];
        
            array_push($data, $jobData);
        }
    
        return $data;
    }

    public function showWorkProgress (Request $request) {
        $jobListing = JobListing::with("tasks")->where('id',$request->jobId)->first();
        $professional = Professional::with("user","profile")->where('id', $request->professionalId)->first();

        $response = [
            "jobDetails"=>$jobListing,
            "professional" => $professional
        ];

        if ($jobListing && $professional) {
            // return $this->success([$response], 200);
            return response()->json([
                'message'=>"Request was successful",
                'data'=>$response,
            ], 200);
        }
    }

    public function confirmEndOfShift(Request $request) {
        $professionalId = $request->professionalId;
        $jobId = $request->jobId;
        $conditions = [
            'professional_id' => $professionalId,
            'job_listing_id' => $jobId,
        ];
        if (Auth::user()->user_type_id == Constants::$business) {
            Professional::where('id',$professionalId)->update(['status'=>'Available']);
            JobListing::where('id',$jobId)->update(['status'=>'Completed']);
            return $this->confirmEndOfShiftNotificationForProfessional($professionalId, $jobId, $conditions);
        }else{
            return $this->notifyBusinessOfCompletion($jobId, $conditions);
        }
    }

    public function markTask (Request $request) {
        $updatedTasks = Task::where('id',$request->taskId)->update(['isCompleted'=>$request->isCompleted]);
        if ($updatedTasks) {
            return $this->success([
                'message' => "Task marked as completed successfully",
            ], 200);
        }
        return $this->error('Error', 'Unable to mark task, please try again later', 404);
        
    }

    private function notifyBusinessOfCompletion ($jobId, $conditions) {

        $jobDetails = JobApplicant::with('jobListing', 'professional.user', 'jobListing.business.profile')->where($conditions)->get();
        
        if ($jobDetails->isEmpty()) {
            return $this->error('Error', 'Job details not found', 404);
        }

        $businessName = $jobDetails[0]['jobListing']['business']['company_name'] ?? null;
        $businessId = $jobDetails[0]['jobListing']['business']['id'];
        $jobTitle = $jobDetails[0]['jobListing']["job_title"] ?? null; 
        $applicantName = $jobDetails[0]['professional']['user']['fname'] ?? null . ' ' . $jobDetails[0]['professional']['user']['lname'] ?? null;
        // $jobPostingDate = Carbon::parse($jobDetails[0]['created_at'])->format('M jS, Y');

        $subject = "Job completion Notice! ";

        $body = "Hello $businessName, I have completed all the tasks listed in the $jobTitle shift you hired me for";
        // $body = "Hello $businessName, I have completed all the tasks listed in the $jobTitle shift you hired me for.
        // Thanks. $applicantName ";

        $notify = Notification::create([
            'business_id' => $businessId,
            'subject' => $subject,
            'body' => $body,
            'job_id' => $jobId,
            'job_type' => "system",
            'read' => false
        ]);

        if (!$notify) {
            return $this->error('Error', 'Notification creation failed', 500);
        }

        return $this->success([
            'message' => "A notification is sent to $businessName",
        ], 200);
    }

    private function confirmEndOfShiftNotificationForProfessional ($professionalId, $jobId, $conditions) {
        $details = JobApplicant::with('jobListing', 'professional.user', 'professional.profile')
        ->where($conditions)->get();

        if ($details->isEmpty()) {
            return $this->error('Error', 'Details not found', 404);
        }

        $businessName = $details[0]['jobListing']['business']['company_name'] ?? null;
        $jobTitle = $details[0]['jobListing']["job_title"] ?? null; 
        $applicantName = $details[0]['professional']['user']['fname'] ?? null . ' ' . $details[0]['professional']['user']['lname'] ?? null;
        // $jobPostingDate = Carbon::parse($details[0]['created_at'])->format('M jS, Y');

        $subject = "Job completion Notice!";

        $body = "Hello $applicantName, $businessName confirmed that you have completed all the tasks listed for $jobTitle that you were hired for";

        $notify = Notification::create([
            'professional_id' => $professionalId,
            'subject' => $subject,
            'body' => $body,
            'job_id' => $jobId,
            'job_type' => "system",
            'read' => false
        ]);

        if (!$notify) {
            return $this->error('Error', 'Notification not sent', 500);
        }

        return $this->success([
            'message' => "A notification is sent to $applicantName",
        ], 200);
    }

}
