<?php

namespace App\Http\Requests;

use App\Models\Permissions;
use App\Models\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PermissionsRequest extends FormRequest
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
        $id = $this->route('permission'); // Assuming the route parameter is 'role'

        if ($this->isMethod('POST')) {
            return [
                'name' => 'required|unique:permissions,name',
            ];
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $item = Permissions::find($id);
            if (!$item) {
                throw new HttpResponseException(response()->json([
                    'check' => false,
                    'msg'   => 'Permission id  not found',
                ], 200)); 
            }
            return [
                'name' => 'required|unique:permissions,name',
            ];
        } elseif ($this->isMethod('DELETE')) {
            return [
                'role_id' => 'exists:permissions,id', // Check existence of role ID
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
            'data' => Roles::all(),
        ], 200));
    }
}
