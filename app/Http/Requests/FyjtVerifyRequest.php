<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FyjtVerifyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token'   => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9]+$/'],
            'captcha' => ['required', 'string', 'size:4'],
        ];
    }

    public function messages()
    {
        return [
            'token.required'   => 'Token 是必填项。',
            'token.regex'      => 'Token 只能包含半角英数字。',
            'captcha.required' => '验证码是必填项。',
            'captcha.size'     => '验证码必须是 4 位。',
        ];
    }
}
