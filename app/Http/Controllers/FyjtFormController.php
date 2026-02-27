<?php

namespace App\Http\Controllers;

use App\Http\Requests\FyjtSubmitRequest;  // 引入 Form Request
use Illuminate\Http\Request;
use App\Models\TInvitationCodeMng;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FyjtFormController extends Controller
{
    public function index()
    {
        // 显示第 1 步：免责声明 + token + captcha
        return view('fyjt-step1');
    }

    /**
     * 第一步：验证 token + captcha
     */
    public function verify(\App\Http\Requests\FyjtVerifyRequest $request)
    {
        $expected = (string) session('fyjt_captcha');
        if ($expected === '' || strcasecmp($request->input('captcha'), $expected) !== 0) {
            return back()->withErrors(['captcha' => '验证码错误，请重试。'])->withInput();
        }

        // 验证 token 格式已由 FyjtVerifyRequest 处理，这里可做额外业务校验（例如远程校验 token 是否有效）
        $strToken = substr(trim($request->input('token')), 0, 255);

        // 检查 $strToken 在 t_invitation_code_mng 表中是否存在且状态为 1
        $code = TInvitationCodeMng::where('invitation_code', $strToken)->where('status', 1)->first();
        if (! $code) {
            return back()->withErrors(['token' => '无效的 Token，请确认后重试。'])->withInput();
        }

        $exists = TInvitationCodeMng::where('invitation_code', $strToken)
            ->where('status',1)
            ->where('operate_times','>=',1)
            ->exists();

        if (! $exists) {
            return back()->withErrors(['token' => 'Token 无效，请重新开始。'])->withInput();
        }


        // 保存 token 与元数据：创建时间、来源 IP、User-Agent、以及是否已使用标志
        $ua = substr($request->header('User-Agent', ''), 0, 255);
        session([
            'fyjt_valid_token' => $strToken,
            'fyjt_step1_passed' => true,
            'fyjt_token_created_at' => time(),
            'fyjt_token_ip' => $request->ip(),
            'fyjt_token_ua' => $ua,
            'fyjt_token_used' => false,
        ]);
        session()->forget('fyjt_captcha');

        return redirect()->route('fyjt.step2');
    }

    /**
     * 显示第 2 步表单（剩余字段与最终提交）
     */
    public function step2()
    {
        // 确保第 1 步已通过
        if (! session()->has('fyjt_valid_token')) {

            return redirect()->route('fyjt.form');
        }

        return view('fyjt-step2');
    }

    public function submit(FyjtSubmitRequest $request)  // 类型提示：FyjtSubmitRequest
    {
        // 走到这里时，表单验证已通过（不再需要 captcha，因为已在第 1 步校验）
        // 确保 token 来自已验证的会话（防止用户伪造）
        $sessionToken = session('fyjt_valid_token');
        $token = $request->input('token');

        // 基础一致性检查
        if (empty($sessionToken) || $sessionToken !== $token) {
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 未通过第一步验证，请重新开始。']);
        }

        // 额外复核：过期、来源 IP/UA、是否已被使用（与中间件重复检查以防绕过）
        $created = (int) session('fyjt_token_created_at', 0);
        $used = (bool) session('fyjt_token_used', false);
        $ip = session('fyjt_token_ip');
        $ua = session('fyjt_token_ua');
        $ttl = 15 * 60; // 与中间件保持一致

        if ($used) {
            session()->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 已被使用，请重新开始。']);
        }

        if ($created === 0 || (time() - $created) > $ttl) {
            session()->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 已过期，请重新开始。']);
        }

        $currentIp = $request->ip();
        $currentUa = substr($request->header('User-Agent', ''), 0, 255);
        if ($ip !== $currentIp || $ua !== $currentUa) {
            session()->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 校验失败（来源信息不匹配），请重新开始。']);
        }

        // 标记为已使用并清理会话
        session()->put('fyjt_token_used', true);
        session()->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);

        $email   = $request->input('email');
        $field3  = $request->input('field3');
        $field4  = $request->input('field4');

        // 调用远程 web 服务（POST），传入必需参数并携带密钥/签名供对方验证
        // 配置项：services.fyjt.endpoint, services.fyjt.secret 或环境变量 FYJT_REMOTE_ENDPOINT, FYJT_REMOTE_SECRET
        $endpoint = config('services.fyjt.endpoint', env('FYJT_REMOTE_ENDPOINT'));
        $secret = config('services.fyjt.secret', env('FYJT_REMOTE_SECRET'));

        if (empty($endpoint) || empty($secret)) {
            Log::error('Fyjt remote service not configured', ['endpoint' => $endpoint ? 'configured' : 'missing', 'has_secret' => !empty($secret)]);
            return back()->withErrors(['remote' => '远程服务未配置，请联系管理员。'])->withInput();
        }

        $payload = [
            'token' => $token,
            'email' => $email,
            'field3' => $field3,
            'field4' => $field4,
            'field5' => $currentIp,
        ];

        // 生成带时间戳与 nonce 的签名，防重放：签名字符串为 timestamp + "\n" + nonce + "\n" + body
        $timestamp = (string) time();
        try {
            $nonce = bin2hex(random_bytes(12));
        } catch (\Exception $e) {
            // fallback
            $nonce = bin2hex(openssl_random_pseudo_bytes(12));
        }
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $toSign = $timestamp . "\n" . $nonce . "\n" . $body;
        $signature = base64_encode(hash_hmac('sha256', $toSign, $secret, true));

        try {
            $response = Http::timeout(59)
                    ->withHeaders([
                        'X-Signature' => $signature,
                        'X-Timestamp' => $timestamp,
                        'X-Nonce' => $nonce,
                        'Accept' => 'application/json',
                    ])
                ->post($endpoint, $payload);
        } catch (\Exception $e) {
            Log::error('Fyjt remote request exception', ['error' => $e->getMessage()]);
            return back()->withErrors(['remote' => '远程服务调用失败，请稍后重试。'])->withInput();
        }

        if (! $response->successful()) {
            Log::error('Fyjt remote request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->withErrors(['remote' => '远程服务返回错误，请稍后重试。'])->withInput();
        }

        $respJson = $response->json();
        // 假设远端返回 JSON 包含 success 字段，依据实际对方接口调整判断
        if (is_array($respJson) && array_key_exists('success', $respJson) && ! $respJson['success']) {
            $msg = isset($respJson['message']) ? $respJson['message'] : (isset($respJson['error']) ? $respJson['error'] : '远程服务返回失败');
            Log::warning('Fyjt remote returned negative', ['response' => $respJson]);
            return back()->withErrors(['remote' => $msg])->withInput();
        }

        // // 可选：记录调用次数 / 来源 IP 到 invitation_code 表（非必需）
        // try {
        //     if ($code) {
        //         $code->increment('operate_times');
        //         $code->user_ip = $request->ip();
        //         $code->update_time = now();
        //         $code->save();
        //     }
        // } catch (\Exception $e) {
        //     Log::warning('Failed to update invitation code meta', ['error' => $e->getMessage()]);
        // }

        return back()->with('success', '提交成功！')->withInput([]);
    }
}