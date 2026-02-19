<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // السماح للمستخدمين المصادق عليهم
    }

    public function rules()
    {
        $userId = $this->route('user')?->id ?? $this->user()->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'birthdate' => 'sometimes|nullable|date',
            'avatar' => 'sometimes|nullable|image|max:2048',
        ];
    }
}
