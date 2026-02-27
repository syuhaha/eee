<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>fyjt — 第一步</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{ --bg:#f6f8ff; --card:#fff; --muted:#6b7280; --text:#0f172a; --accent:#4f46e5; --border:rgba(15,23,42,0.06); --radius:12px; --font:'DM Sans',system-ui,-apple-system, sans-serif }
    *{box-sizing:border-box}
    body{margin:0;font-family:var(--font);background:linear-gradient(180deg,#f6f8ff,#eef2ff);padding:36px 16px;display:flex;justify-content:center}
    .wrap{width:100%;max-width:720px}
    .card{background:var(--card);border-radius:var(--radius);padding:22px;border:1px solid var(--border);box-shadow:0 8px 24px rgba(15,23,42,0.06)}
    .header{display:flex;gap:12px;align-items:center;margin-bottom:8px}
    .logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#06b6d4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    h1{margin:0;font-size:18px}
    .subtitle{color:var(--muted);font-size:13px}
    .disclaimer{font-size:13px;color:var(--muted);background:linear-gradient(180deg,rgba(15,23,42,0.01),transparent);border-radius:10px;padding:12px;border:1px dashed rgba(15,23,42,0.04);margin:12px 0}
    form{display:flex;flex-direction:column;gap:30px}
    label{font-weight:600}
    input,textarea{padding:10px 12px;border-radius:8px;border:1px solid var(--border);width:100%;box-sizing:border-box}
    .captcha{display:flex;gap:10px;align-items:center}
    .captcha input{flex:1;min-width:0}
    .btn{background:linear-gradient(90deg,var(--accent),#06b6d4);color:#fff;border:none;padding:10px 14px;border-radius:10px;cursor:pointer}
    .btn-outline{background:transparent;border:1px solid rgba(79,70,229,0.14);padding:8px 10px;border-radius:8px}
    .hint{font-size:12px;color:var(--muted)}
    .err{color:#dc2626}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="header">
        <div class="logo">FY</div>
        <div>
          <h1>fyjt — 第一步</h1>
          <div class="subtitle">请输入 Token 并通过验证码验证后继续下一步。</div>
        </div>
      </div>

      <?php if($errors->any()): ?>
        <div class="err">请修正以下错误并重试。</div>
      <?php endif; ?>

      <div class="disclaimer">
        <strong>科目1（免责文字）</strong><br>
        本页面用于提交信息。你确认所填内容真实有效；因填写信息错误、第三方不可用等导致的结果由提交方自行承担。
      </div>

      <form method="POST" action="<?php echo e(route('fyjt.verify')); ?>" id="fyjtStep1" novalidate>
        <?php echo csrf_field(); ?>

        <div>
          <label for="token">aaa（）</label>
          <input id="token" name="token" type="text" maxlength="64" pattern="[A-Za-z0-9]{1,64}" inputmode="latin" autocomplete="off" value="<?php echo e(old('token')); ?>" required placeholder="请输入 Token">
          <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <div class="hint">仅允许半角英数字，长度不超过 64。</div>
        </div>

        <div>
          <label>CAPTCHA</label>
          <div class="captcha">
            <input name="captcha" type="text" maxlength="4" pattern="[A-Za-z0-9]{4}" inputmode="latin" autocomplete="off" required placeholder="输入验证码">
            <img id="captchaImg" src="<?php echo e(route('fyjt.captcha')); ?>?t=<?php echo e(time()); ?>" alt="captcha" title="点击刷新" style="height:46px;border-radius:8px;border:1px solid var(--border);cursor:pointer">
            <button type="button" class="btn-outline" id="refreshBtn">刷新</button>
          </div>
          <?php $__errorArgs = ['captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <div class="hint">点击图片或“刷新”更换验证码。</div>
        </div>

        <div style="text-align:center;margin-top:6px">
          <button class="btn" type="submit">下一步</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      var img = document.getElementById('captchaImg');
      var btn = document.getElementById('refreshBtn');
      if (!img || !btn) return;
      var COOLDOWN_MS = 3000; var lastAt = 0; var btnText = btn.textContent; var timer = null;
      function setCooldown(ms){ clearInterval(timer); btn.disabled=true; var start=Date.now(); timer=setInterval(function(){var left=Math.max(0, ms-(Date.now()-start)); if(left<=0){clearInterval(timer);btn.disabled=false;btn.textContent=btnText;} else {btn.textContent=btnText+'（'+Math.ceil(left/1000)+'s）';}},200); }
      function doRefresh(){ var now=Date.now(); if(now-lastAt<COOLDOWN_MS) return; lastAt=now; img.style.opacity='0.25'; img.style.transform='scale(.98)'; setTimeout(function(){ img.src='<?php echo e(route('fyjt.captcha')); ?>?t='+now; img.onload=function(){ img.style.opacity='1'; img.style.transform='scale(1)'; }; },160); setCooldown(COOLDOWN_MS); }
      img.addEventListener('click', doRefresh); btn.addEventListener('click', doRefresh);

      // 在提交前将输入值左右两边 trim 并写回 input
      var form1 = document.getElementById('fyjtStep1');
      if (form1) {
        form1.addEventListener('submit', function (e) {
          var tokenEl = form1.querySelector('[name="token"]');
          var captchaEl = form1.querySelector('[name="captcha"]');
          [tokenEl, captchaEl].forEach(function (el) {
            if (!el) return;
            if (typeof el.value === 'string') {
              var v = el.value.trim();
              if (el.value !== v) el.value = v;
            }
          });

          // 基于 trim 后的值进行客户端校验（替代浏览器内建校验）
          if (tokenEl) tokenEl.setCustomValidity('');
          if (captchaEl) captchaEl.setCustomValidity('');
          var ok = true;
          if (!tokenEl || !/^[A-Za-z0-9]{1,64}$/.test(tokenEl.value)) { if (tokenEl) tokenEl.setCustomValidity('Token 仅允许半角英数字，长度 1-64。'); ok = false; }
          if (!captchaEl) { ok = false; }
          else {
            var cap = (captchaEl.value||'').trim();
            // 必须 4 位、仅字母数字、并且不含易混淆字符 1 l i I O o 0 Q q
            if (cap.length !== 4 || !/^[A-Za-z0-9]{4}$/.test(cap) || /[1liIOo0Q]/.test(cap)) {
              captchaEl.setCustomValidity('验证码格式错误。');
              ok = false;
            }
          }

          if (!ok) {
            e.preventDefault();
            var firstInvalid = form1.querySelector(':invalid');
            if (firstInvalid) firstInvalid.reportValidity();
          }
        });
      }
    })();
  </script>
</body>
</html>
<?php /**PATH F:\phpstudy81\WWW\fyjt\resources\views/fyjt-step1.blade.php ENDPATH**/ ?>