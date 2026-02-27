<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FyjtSubmitRequest extends FormRequest
{
    /**
     * 判断用户是否有权限提交（一般返回 true，除非需要特殊权限）
     */
    public function authorize()
    {
        return true;  // 所有人都能提交，若需要登录则 return auth()->check();
    }

    /**
     * 验证规则
     */
    public function rules()
    {
        return [
            'token'   => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9]+$/'],
            'email'   => ['required', 'email', 'max:200'],
            'field3'  => ['required', 'string', 'max:50', 'regex:/^[0-9A-Za-z\x{4e00}-\x{9fa5}]+$/u'],
            'field4'  => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * 自定义错误消息（可选）
     */
    public function messages()
    {
        return [
            'token.required'   => 'Token 是必填项。',
            'token.regex'      => 'Token 只能包含半角英数字。',
            'email.required'   => 'Email 是必填项。',
            'email.email'      => 'Email 格式不正确。',
            'email.max' => 'Email 长度不能超过 200。',
            'field3.required'  => '科目3 是必填项。',
            'field3.regex'     => '科目3 只能包含数字、英文、中文字符。',
            'field4.required'  => '科目4 是必填项。',
        ];
    }

    /**
     * 自定义属性名称（可选，错误消息里显示更友好）
     */
    public function attributes()
    {
        return [
            'token'   => 'Token',
            'email'   => 'Email',
            'field3'  => '科目3',
            'field4'  => '科目4',
            'captcha' => '验证码',
        ];
    }
}