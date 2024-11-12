<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $id = $this->route('user'); 
        if ($this->isMethod('POST')) {
            return [
                'name' => 'required|unique:users,name',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'role_id'=>'required|exists:roles,id'
            ];
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $item = User::find($id);
            if (!$item) {
                throw new HttpResponseException(response()->json([
                    'check' => false,
                    'msg'   => 'User id  not found',
                ], 200)); 
            }
            
        } elseif ($this->isMethod('DELETE')) {
            return [
                'role_id' => 'exists:roles,id', // Check existence of role ID
            ];
        }

        return [];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'check' => false,
            'msg' => $validator->errors()->first(),
            'errors' => $validator->errors(),
            'data' => User::all(),
        ], 200));
    }
}
