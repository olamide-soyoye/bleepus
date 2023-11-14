<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fname' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'user_type_id' => 'required|in:1,2',
            'longitude' => 'required_if:user_type_id,1,2',
            'latitude' => 'required_if:user_type_id,1,2',
            'company_name' => 'required_if:user_type_id,2',
        ];
        // return [
        //     'fname' => 'required|string',
        //     'lname' => 'required|string',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|min:8',
        //     'user_type_id' => 'required|in:1,2,3',
        //     'longitude' => 'required_if:user_type_id,1,2,3',
        //     'latitude' => 'required_if:user_type_id,1,2,3',
        //     'company_name' => 'required_if:user_type_id,2',
        // ];
    }
}
