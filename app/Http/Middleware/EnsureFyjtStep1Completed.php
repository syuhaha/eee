<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFyjtStep1Completed
{
    /**
     * Handle an incoming request.
     * If the session does not have the validated token, redirect to step 1.
     */
    public function handle(Request $request, Closure $next)
    {
        $session = session();

        $token = $session->get('fyjt_valid_token');
        if (empty($token)) {
            return redirect()->route('fyjt.form');
        }

        // 强化校验：过期、来自同一 IP/UA、且未被使用
        $created = (int) $session->get('fyjt_token_created_at', 0);
        $used = (bool) $session->get('fyjt_token_used', false);
        $ip = $session->get('fyjt_token_ip');
        $ua = $session->get('fyjt_token_ua');

        $ttl = 15 * 60; // 15 分钟有效期，可按需调整或从配置读取

        if ($used) {
            $session->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 已被使用，请重新开始。']);
        }

        if ($created === 0 || (time() - $created) > $ttl) {
            $session->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 已过期，请重新开始。']);
        }

        $currentIp = $request->ip();
        $currentUa = substr($request->header('User-Agent', ''), 0, 255);
        if ($ip !== $currentIp || $ua !== $currentUa) {
            $session->forget(['fyjt_valid_token', 'fyjt_step1_passed', 'fyjt_token_created_at', 'fyjt_token_ip', 'fyjt_token_ua', 'fyjt_token_used']);
            return redirect()->route('fyjt.form')->withErrors(['token' => 'Token 校验失败（来源信息不匹配），请重新开始。']);
        }

        return $next($request);
    }
}
