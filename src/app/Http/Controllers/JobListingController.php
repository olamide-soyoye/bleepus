<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

class JobListingController extends Controller
{
    use HttpResponses;

    public function createJobOffer(Request $request){
        $validatedData = $request->validate([
            'job_title' => 'required|string|max:255',
            'job_description' => 'required|string',
            'wage' => 'required|numeric',
        ]);
        

        $JobListing = JobListing::create([
            'title' => $validatedData['job_title'],
            'description' => $validatedData['job_description'],
            'wage' => $validatedData['wage'],
        ]);

        if ($JobListing) {
            return $this->success([
                'data' => $JobListing,
            ], 200);
        }
        return $this->error('Error', 'Could not post job', 400);
    } 
}
