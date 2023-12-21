<?php

namespace App\Http\Controllers;

use App\Models\JobApplicant;
use App\Models\JobListing;
use App\Models\Notification;
use App\Models\Professional;
use App\Models\Profile;
use Carbon\Carbon;
use Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Traits\HttpResponses;

class JobApplicantController extends Controller
{
    use HttpResponses;

    public function applyForJobs(Request $request) {
        $user = Auth::user();

        if ($user->user_type_id !== Constants::$professional) {
            return $this->error('Error', 'Only healthcare Professionals are allowed to apply for jobs', 400);
        }

        try {
            $validatedData = $request->validate([
                'jobId' => 'required|numeric',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Validation Error', $e->getMessage(), 422);
        }

        $professional = Profile::with("user", "professional")->where('user_id', $user->id)->first();

        if (!$professional) {
            return $this->error('Error', 'Professional profile not found', 404);
        }

        $professionalId = $professional["professional"]["id"];
        
        // Check if the user has already applied for the job
        $existingApplication = JobApplicant::where([
            'professional_id' => $professionalId,
            'job_listing_id' => $validatedData["jobId"],
        ])->exists();

        if ($existingApplication) {
            return $this->error('Error', 'You have already applied for this job', 400);
        }

        $apply = JobApplicant::create([
            "professional_id" => $professionalId,
            "job_listing_id" => $validatedData["jobId"]
        ]);

        if (!$apply) {
            return $this->error('Error', 'Unable to apply for job', 400);
        }

        $conditions = [
            'professional_id' => $professionalId,
            'job_listing_id' => $validatedData["jobId"],
        ];

        $jobDetails = JobApplicant::with('jobListing', 'professional.user', 'jobListing.business.profile')->where($conditions)->get();
        
        if ($jobDetails->isEmpty()) {
            return $this->error('Error', 'Job details not found', 404);
        }

        $businessName = $jobDetails[0]['jobListing']['business']['company_name'] ?? null;
        $businessId = $jobDetails[0]['jobListing']['business']['id'];
        $jobTitle = $jobDetails[0]['jobListing']["job_title"] ?? null; 
        $applicantName = $jobDetails[0]['professional']['user']['fname'] ?? null . ' ' . $jobDetails[0]['professional']['user']['lname'] ?? null;
        $jobPostingDate = Carbon::parse($jobDetails[0]['created_at'])->format('M jS, Y');

        $subject = "Hello $businessName, I am interested in your shift offer! ";
        $body = "
            Hello $businessName, I am interested in the $jobTitle shift you posted on $jobPostingDate. 
           \n Thanks. \n $applicantName
        ";

        $notify = Notification::create([
            'business_id' => $businessId,
            'professional_id' => $professionalId,
            'subject' => $subject,
            'body' => $body,
            'job_id' => $validatedData["jobId"]
        ]);

        if (!$notify) {
            return $this->error('Error', 'Notification creation failed', 500);
        }

        return $this->success([
            'message' => 'Successfully Applied',
        ], 200);
    }

    public function getAllJobsAppliedFor(){
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$professional) {
            $professional = Profile::with("user","professional")->where('user_id', $user_id)->first();
            $professionalId = $professional["professional"]["id"];

            $jobsAppliedFor = JobApplicant::join("job_listings", "job_applicants.job_listing_id","job_listings.id")
            ->leftjoin("businesses","job_listings.business_id","businesses.id")
            ->leftJoin("profiles","businesses.profile_id","profiles.id")
            ->select("profiles.profile_pic","businesses.company_name","job_listings.*","profiles.address as jobAddress")
            ->where("professional_id", $professionalId)
            ->get();
            
            if ($jobsAppliedFor) {
                return $this->success([
                    'JobsAppliedFor' => $jobsAppliedFor->isEmpty() ? [] : $jobsAppliedFor,
                ], 200);
            }
        } 
        return $this->error('Error', 'Only Healthcare Professionals can view jobs applied for', 400);
    }

    public function getApplicants(Request $request){
        // send jobId
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$business) {
            $business = Profile::with("user","business")->where('user_id', $user_id)->first();
            $businessId = $business["business"]["id"];

            $jobApplicants = JobApplicant::join("job_listings", "job_applicants.job_listing_id","job_listings.id")
            ->join("businesses","job_listings.business_id","businesses.id")
            ->join("profiles","businesses.profile_id","profiles.id")
            ->join("professionals","job_applicants.professional_id","professionals.id")
            ->join("users","professionals.user_id","users.id")
            ->select(
                'professionals.id as proffessionalId',
                'professionals.user_id',
                'professionals.profile_id',
                'professionals.max_distance',
                'professionals.profession_title',
                'professionals.skills',
                'professionals.certifications',
                'professionals.years_of_experience',
                'professionals.wage',
                'professionals.ratings',
                'professionals.specialities',

                'job_listings.id as jobId',
                'job_listings.job_title',
                'job_listings.job_description',
                'job_listings.wage',
                'job_listings.business_id',
                'job_listings.availability',
                'job_listings.job_type_id',
                'job_listings.duration',
                'job_listings.start_date',
                'job_listings.end_date',
                'job_listings.qualifications',
                'job_listings.urgency',
                'job_listings.tasks',
                'job_listings.payment_status',

                "profiles.phone_no","profiles.total_earnings","profiles.longitude", "profiles.latitude","profiles.id as profileId",
                "profiles.about","users.fname","users.lname", "users.id as UserId")
                ->where("job_listings.business_id", $businessId)
                ->orderBy('job_listings.job_title')
                ->orderBy('job_listings.id')
                ->get();

            
            if ($jobApplicants) {
                $data = $jobApplicants->isEmpty() ? [] : $this->formatGetApplicantsList($jobApplicants);
                return $this->success([
                    $data,
                ], 200);
            }
        } 
        return $this->error('Error', 'Only Healthcare Providers can view jobs applicants', 400);
    }

    public function hireOrRejectProfessionals(Request $request)
    {
        if (Auth::user()->user_type_id == Constants::$business) {
            $professionalId = $request->professionalId;
            $jobId = $request->jobId;
            $decision = $request->decision;
            
            $conditions = [
                'professional_id' => $professionalId,
                'job_listing_id' => $jobId,
            ];
            
            $hireOrReject = $this->updateJobApplicantStatus($conditions, $decision);

            if ($decision === "Hired") {
                Professional::where('id',$professionalId)->update(['status'=>'Occupied']);
            }

            if ($hireOrReject) {
                $jobDetails = $this->getJobDetails($conditions);

                $notification = $this->createNotification($professionalId, $decision, $jobDetails);

                if ($notification) {
                    return $this->success(['message' => $decision], 200);
                }
            }
        }

        return $this->error('Error', 'Only Healthcare Providers can hire', 400);
    }

    private function updateJobApplicantStatus(array $conditions, string $decision)
    {
        return JobApplicant::where($conditions)->update(['status' => $decision]);
    }

    private function getJobDetails(array $conditions)
    {
        return JobApplicant::with('jobListing', 'professional.user', 'jobListing.business.profile')->where($conditions)->get()[0] ?? null;
    }

    private function createNotification($professionalId, $decision, $jobDetails) {
        if (!$jobDetails) {
            return null;
        }


        $applicantName = $this->getApplicantName($jobDetails);
        $jobTitle = $jobDetails['jobListing']["job_title"] ?? null;
        $jobId = $jobDetails['jobListing']["id"] ?? null;
        $applicationDate = Carbon::parse($jobDetails['created_at'])->format('M jS, Y');
        $businessPhoneNumber = $jobDetails['jobListing']['business']['profile']['phone_no'] ?? null;
        $businessName = $jobDetails['jobListing']['business']['company_name'] ?? null;
        $subject = $decision === 'Hired' ? "Congratulations, you have been hired" : "Sorry, you were not selected";

        $body = $this->getBodyText($decision, $applicantName, $jobTitle, $applicationDate, $businessPhoneNumber, $businessName);

        return Notification::create([
            'professional_id' => $professionalId,
            'subject' => $subject,
            'body' => $body,
            'job_id' => $jobId,
        ]);
    }

    private function getApplicantName($jobDetails){
        return ($jobDetails['professional']['user']['fname'] ?? null) . ' ' . ($jobDetails['professional']['user']['lname'] ?? null);
    }

    private function getBodyText($decision, $applicantName, $jobTitle, $applicationDate, $businessPhoneNumber, $businessName){
        switch ($decision) {
            case 'Hired':
                return "Hello $applicantName;

                        We are pleased to inform you that you have been hired for the $jobTitle shift that you applied for
                        on $applicationDate. Please call $businessPhoneNumber for detailed information.

                        $businessName";
            case 'Rejected':
                return "Hello $applicantName; 
                
                        We are sorry to inform you that you were not hired for the $jobTitle shift that you applied for
                        on $applicationDate. We hope to have you with us on some other opportunities. 

                        $businessName";
            default:
                return '';
        }
    }

    private function formatGetApplicantsList($jobApplicants) {
        $data = [
            'job_listings' => [
                'id' => $jobApplicants[0]->jobId,
                'job_title' => $jobApplicants[0]->job_title,
                'job_description' => $jobApplicants[0]->job_description,
                'wage' => $jobApplicants[0]->wage,
                'availability' => $jobApplicants[0]->availability,
                'duration' => $jobApplicants[0]->duration,
                'start_date' => $jobApplicants[0]->start_date,
                'end_date' => $jobApplicants[0]->end_date,
                'qualifications' => $jobApplicants[0]->qualifications,
                'urgency' => $jobApplicants[0]->urgency,
                'tasks' => $jobApplicants[0]->tasks,
                'payment_status' => $jobApplicants[0]->payment_status,
            ],
            'professionals' => [
                'id' => $jobApplicants[0]->proffessionalId,
                'max_distance' => $jobApplicants[0]->max_distance,
                'total_earnings' => $jobApplicants[0]->total_earnings,
                'skills' => $jobApplicants[0]->skills,
                'certifications' => $jobApplicants[0]->certifications,
                'years_of_experience' => $jobApplicants[0]->years_of_experience,
                'wage' => $jobApplicants[0]->wage,
                'ratings' => $jobApplicants[0]->ratings,
                'specialities' => $jobApplicants[0]->specialities,
            ],
            'profiles' => [
                'id' => $jobApplicants[0]->profileId,
                'phone_no' => $jobApplicants[0]->phone_no,
                'total_earnings' => $jobApplicants[0]->total_earnings,
                'longitude' => $jobApplicants[0]->longitude,
                'latitude' => $jobApplicants[0]->latitude,
                'about' => $jobApplicants[0]->about,
            ],
            'users' => [
                'id' => $jobApplicants[0]->UserId,
                'fname' => $jobApplicants[0]->fname,
                'lname' => $jobApplicants[0]->lname
            ],
        ];
        return $data;
    }

    public function getAllJobsHiredFor(){
        $user_id = Auth::id();
        if (Auth::user()->user_type_id == Constants::$professional) {
            $professional = Profile::with("user","professional")->where('user_id', $user_id)->first();
            $professionalId = $professional["professional"]["id"];
            $jobsHiredFor = JobApplicant::with("jobListing","jobListing.tasks","jobListing.business","jobListing.business.profile")
            ->where('professional_id', $professionalId)
            ->where('status', "Hired")
            ->get();
            // return $jobsHiredFor; 
            $pendingCount = JobApplicant::where('professional_id', $professionalId)->where('status', 'Pending')->count();

            $totalCount = JobApplicant::where('professional_id', $professionalId)->count();

            $hiredCount = JobApplicant::where('professional_id', $professionalId)->where('status', 'Hired')->count();


            // Prepare the response
            $response = [
                'pending' => $pendingCount,
                'total' => $totalCount,
                'hired' => $hiredCount,
            ];
            
            if ($jobsHiredFor) {
                $data = [
                    "analytics" => $response,
                    "jobsHiredFor"=>$jobsHiredFor->isEmpty() ? [] : $jobsHiredFor
                ];
                return $this->success([
                    $data,
                ], 200); 
            }
        } 
        return $this->error('Error', 'Only Healthcare Professionals can view jobs hired for', 400);
    }
}
