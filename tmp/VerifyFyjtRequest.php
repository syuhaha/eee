<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * VerifyFyjtRequest
 *
 * 说明：该中间件用于接收来自客户端（`FyjtFormController` 发出的签名请求），
 * 校验请求头中的 `X-Timestamp`, `X-Nonce`, `X-Signature`，并对请求体进行验签。
 * 验签通过后将解析的 JSON body 合并到 `$request->input()`，并在 `$request->attributes`
 * 中设置 `fyjt_verified = true` 以便控制器安全读取已验证的参数。
 *
 * 安全要点：
 * - 使用时间戳 + nonce 防重放；nonce 需在缓存中做幂等插入（Cache::add）。
 * - 使用 HMAC-SHA256（二进制输出）然后 base64 编码作为签名字符串。
 * - 用 `hash_equals` 做常量时间比较以避免时序攻击。
 * - 推荐使用 Redis 等并发安全的 cache 驱动以保证 nonce 判重的正确性。
 */
class VerifyFyjtRequest
{
    /**
     * 处理请求。
     *
     * 验证步骤（严格顺序）：
     * 1. 从 Header 读取 `X-Timestamp`, `X-Nonce`, `X-Signature`；缺一则拒绝。
     * 2. 检查时间偏差是否在允许范围内（防止重放与过期请求）。
     * 3. 使用 Cache::add 将 nonce 写入缓存（原子化插入）。若已存在则认为是重放并拒绝。
     * 4. 读取原始请求体（未被框架转换的原始 bytes），按约定构造签名原文：
     *    `timestamp + "\n" + nonce + "\n" + body`。
     * 5. 获取服务器端共享密钥：支持 env 存储 base64（中间件会尝试解码），也支持明文密钥。
     * 6. 用 HMAC-SHA256 计算二进制 MAC，再 base64 编码，与请求头中 `X-Signature` 比对。
     * 7. 若通过，将 JSON body 解析并 merge 到 `$request->input()`，并标记为已验证。
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1) 读取并基本存在性检查
        $ts = $request->header('X-Timestamp');
        $nonce = $request->header('X-Nonce');
        $signature = $request->header('X-Signature');

        if (! $ts || ! $nonce || ! $signature) {
            // 没有完整签名信息，直接返回 401
            return response()->json(['error' => 'Missing signature headers'], Response::HTTP_UNAUTHORIZED);
        }

        // 2) 时间偏差校验（单位：秒），从 env 可配置，默认 300s
        $drift = (int) env('FYJT_REMOTE_DRIFT', 300);
        $now = time();
        // 如果客户端时间与服务器时间偏差过大，拒绝请求
        if (abs($now - (int)$ts) > $drift) {
            Log::warning('Fyjt signature timestamp drift', ['now' => $now, 'ts' => $ts]);
            return response()->json(['error' => 'Timestamp drift too large'], Response::HTTP_UNAUTHORIZED);
        }

        // 3) 防重放：nonce 写入缓存（原子插入，失败表示已使用过）
        $nonceTtl = (int) env('FYJT_NONCE_TTL', 300);
        $cacheKey = 'fyjt_nonce_' . $nonce;
        // Cache::add 在 key 已存在时返回 false，能作为原子性判断
        if (! Cache::add($cacheKey, true, $nonceTtl)) {
            Log::warning('Fyjt nonce replay', ['nonce' => $nonce]);
            return response()->json(['error' => 'Nonce already used'], Response::HTTP_UNAUTHORIZED);
        }

        // 4) 读取原始请求体（注意：这个是未被框架解析过的原始 bytes）
        $body = (string) $request->getContent();

        // 5) 按协议构造待签名字符串：timestamp + "\n" + nonce + "\n" + body
        $toSign = $ts . "\n" . $nonce . "\n" . $body;

        // 6) 获取共享密钥：env 中推荐使用 base64 表示密钥，便于在配置管理中使用
        $secretEnv = env('FYJT_REMOTE_SECRET', '');
        // 尝试严格解码 base64；若解码失败则当作明文密钥使用
        // $key = base64_decode($secretEnv, true);
        // if ($key === false) {
        //     $key = $secretEnv;
        // }
        $key = $secretEnv;

        // 7) 计算 HMAC-SHA256（二进制），再做 base64 编码，与客户端的 header 比较
        $calc = base64_encode(hash_hmac('sha256', $toSign, $key, true));
        // 使用 hash_equals 做常量时间比较以防侧信道泄露
        if (! hash_equals($calc, $signature)) {
            Log::warning('Fyjt signature mismatch', ['expected' => $calc, 'received' => $signature]);
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        }

        // 8) 验签通过：尝试将 JSON body 解析并合并到 request->input()，方便控制器直接访问
        $data = json_decode($body, true);
        if (is_array($data)) {
            // 合并会覆盖同名输入项，且保留框架原有 input() 行为
            $request->merge($data);
        }

        // 标记请求已通过验证（控制器可检查该属性以确保请求来自受信任的客户端）
        $request->attributes->set('fyjt_verified', true);

        // 继续请求处理流程
        return $next($request);
    }
}
