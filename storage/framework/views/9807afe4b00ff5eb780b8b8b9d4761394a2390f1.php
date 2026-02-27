<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>fyjt</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg-main: linear-gradient(180deg, #f6f8ff 0%, #f3f7fb 30%, #eef2ff 100%);
      --card-bg: rgba(255,255,255,0.98);
      --muted: #6b7280;
      --text: #0f172a;
      --accent: #4f46e5;
      --accent-2: #06b6d4;
      --shadow: 0 10px 30px rgba(15,23,42,0.08);
      --radius: 14px;
      --border: rgba(15,23,42,0.06);
      --font: 'DM Sans', system-ui, -apple-system, sans-serif;
      --success: #059669;
      --error: #dc2626;
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:var(--font);
      background: var(--bg-main);
      color:var(--text);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      padding:36px 18px;
      display:flex;
      align-items:flex-start;
      justify-content:center;
      min-height:100vh;
      line-height:1.6;
    }

    .wrap{
      width:100%;
      max-width:720px;
      padding:20px;
    }

    .card{
      background:var(--card-bg);
      border-radius:var(--radius);
      padding:26px;
      box-shadow:var(--shadow);
      border:1px solid var(--border);
    }

    .header{
      display:flex;
      align-items:center;
      gap:14px;
      margin-bottom:8px;
    }
    .logo{
      width:56px;
      height:56px;
      border-radius:12px;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:700;
      font-size:20px;
      box-shadow: 0 10px 24px rgba(79,70,229,0.12);
    }
    .title{
      margin:0;
      font-size:20px;
      font-weight:700;
      letter-spacing:-0.02em;
    }
    .subtitle{ font-size:13px; color:var(--muted) }

    .msg{
      padding:12px 14px;
      border-radius:10px;
      margin:14px 0;
      font-size:14px;
      font-weight:500;
    }
    .msg.ok{ background:#ecfdf5; color:var(--success); border:1px solid #bbf7d0; }
    .msg.bad{ background:#fff1f2; color:var(--error); border:1px solid #fecaca; }

    .disclaimer{
      font-size:13px;
      color:var(--muted);
      background:linear-gradient(180deg, rgba(15,23,42,0.01), transparent);
      border-radius:10px;
      padding:12px;
      border:1px dashed rgba(15,23,42,0.04);
      margin-bottom:14px;
    }

    form { display:flex; flex-direction:column; gap:14px; }

    .field{ display:flex; flex-direction:column; gap:8px; }
    label{ font-weight:600; font-size:14px; color:var(--text) }

    input[type="text"],
    input[type="email"],
    textarea{
      width:100%;
      padding:12px 14px;
      border-radius:10px;
      border:1px solid var(--border);
      background:linear-gradient(180deg, rgba(255,255,255,0.9), #fff);
      font-size:14px;
      transition: box-shadow .18s, border-color .15s, transform .06s;
    }
    input:focus, textarea:focus{
      outline:none;
      border-color: rgba(99,102,241,0.9);
      box-shadow: 0 8px 26px rgba(79,70,229,0.08);
      transform: translateY(-1px);
    }

    textarea{ min-height:120px; resize:vertical; line-height:1.6; }

    .hint{ font-size:12px; color:var(--muted) }
    .err{ font-size:13px; color:var(--error) }

    .captcha{
      display:flex;
      gap:10px;
      align-items:center;
      flex-wrap:wrap;
    }
    .captcha img{
      height:46px;
      border-radius:8px;
      border:1px solid var(--border);
      cursor:pointer;
      transition:opacity .15s, transform .12s;
    }
    .captcha img:hover{ opacity:0.95; transform:translateY(-2px); }

    .btn{
      background:linear-gradient(90deg,var(--accent),var(--accent-2));
      color:#fff;
      border:none;
      padding:12px 18px;
      border-radius:12px;
      cursor:pointer;
      font-weight:700;
      font-size:15px;
      box-shadow: 0 10px 30px rgba(79,70,229,0.12);
      transition:transform .08s, opacity .12s;
    }
    .btn:hover{ transform:translateY(-2px); opacity:0.98; }
    .btn:active{ transform:translateY(0); }

    .btn-outline{
      background:transparent;
      color:var(--accent);
      border:1px solid rgba(79,70,229,0.14);
      padding:10px 12px;
    }

    .submit-row{ text-align:center; margin-top:6px }

    @media (max-width:560px){
      body{ padding:20px 12px }
      .wrap{ padding:0 }
      .logo{ width:48px; height:48px; font-size:18px }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="header">
        <div class="logo">FY</div>
        <div>
          <h1 class="title">fyjt</h1>
          <div class="subtitle">请填写表单并提交 — 客户端已增强校验。</div>
        </div>
      </div>

      <?php if(session('success')): ?>
        <div class="msg ok"><?php echo e(session('success')); ?></div>
      <?php endif; ?>

      <?php if($errors->any()): ?>
        <div class="msg bad">请检查表单输入后重试。</div>
      <?php endif; ?>

      <div class="disclaimer">
        <strong>科目1（免责文字）</strong><br>
        本页面用于提交信息。你确认所填内容真实有效；因填写信息错误、第三方不可用等导致的结果由提交方自行承担。
      </div>

      <form method="POST" action="<?php echo e(route('fyjt.submit')); ?>" id="fyjtForm" novalidate>
        <?php echo csrf_field(); ?>

        <div class="field">
          <label for="token">aaa（）</label>
          <input id="token" name="token" type="text" maxlength="64" pattern="[A-Za-z0-9]{1,64}" inputmode="latin" autocomplete="off" value="<?php echo e(old('token')); ?>" required placeholder="请输入 Token（半角英数字）">
          <div class="hint">仅允许半角英数字，长度不超过 64。</div>
          <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
          <label for="email">bbb（Email）</label>
          <input id="email" name="email" type="email" maxlength="200" autocomplete="email" value="<?php echo e(old('email')); ?>" required placeholder="name@example.com">
          <div class="hint">Email 必填，最长 200。</div>
          <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
          <label for="field3">ccc&nbsp;&nbsp;<span class="hint" id="field3Count">0 / 50</span></label>
          <input id="field3" name="field3" type="text" maxlength="50" autocomplete="off" value="<?php echo e(old('field3')); ?>" required placeholder="科目3（数字/英文/中文）">
          <div class="hint">仅允许数字/英文/中文字符，长度不超过 50。</div>
          <?php $__errorArgs = ['field3'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
          <label for="field4">ddd&nbsp;&nbsp;<span class="hint" id="field4Count">0 / 200</span></label>
          <textarea id="field4" name="field4" maxlength="200" required placeholder="请输入内容（不超过200字）"><?php echo e(old('field4')); ?></textarea>
          <div class="hint">必填，长度不超过 200。</div>
          <?php $__errorArgs = ['field4'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
          <label>CAPTCHA</label>
          <div class="captcha">
            <input name="captcha" type="text" maxlength="4" pattern="[A-Za-z0-9]{4}" inputmode="latin" autocomplete="off" required placeholder="输入验证码">
            <img id="captchaImg" src="<?php echo e(route('fyjt.captcha')); ?>?t=<?php echo e(time()); ?>" alt="captcha" title="点击刷新">
            <button type="button" class="btn-outline" id="refreshBtn">刷新</button>
          </div>
          <div class="hint">点击验证码图片或“刷新”可更换验证码。</div>
          <?php $__errorArgs = ['captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="err"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="submit-row">
          <button class="btn" type="submit">提交</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function () {
      function updateCount(id, outId, max){
        var el = document.getElementById(id), out = document.getElementById(outId);
        if (!el || !out) return;
        function fn(){ out.textContent = el.value.length + ' / ' + max; }
        el.addEventListener('input', fn);
        fn();
      }
      updateCount('field3','field3Count',50);
      updateCount('field4','field4Count',200);

      var img = document.getElementById('captchaImg');
      var btn = document.getElementById('refreshBtn');
      if (img && btn) {
        var COOLDOWN_MS = 4000;
        var lastAt = 0;
        var btnText = btn.textContent;
        var timer = null;

        function setCooldown(ms){
          clearInterval(timer);
          btn.disabled = true;
          var start = Date.now();
          timer = setInterval(function(){
            var left = Math.max(0, ms - (Date.now() - start));
            if (left <= 0){ clearInterval(timer); btn.disabled = false; btn.textContent = btnText; }
            else { btn.textContent = btnText + '（' + Math.ceil(left/1000) + 's）'; }
          }, 200);
        }

        function doRefresh(){
          var now = Date.now();
          if (now - lastAt < COOLDOWN_MS) return;
          lastAt = now;
          img.style.transition = 'opacity .18s, transform .18s';
          img.style.opacity = '0.25';
          img.style.transform = 'scale(.98)';
          setTimeout(function(){
            img.src = "<?php echo e(route('fyjt.captcha')); ?>?t=" + now;
            img.onload = function(){
              img.style.opacity = '1';
              img.style.transform = 'scale(1)';
            };
          }, 160);
          setCooldown(COOLDOWN_MS);
        }

        img.addEventListener('click', doRefresh);
        btn.addEventListener('click', doRefresh);
      }

      var form = document.getElementById('fyjtForm');
      if (!form) return;

      function q(name){ return form.querySelector('[name="' + name + '"]'); }
      function setErr(el, msg){ if (!el) return; el.setCustomValidity(msg || ''); }
      function clearOnInput(el){ if (!el) return; el.addEventListener('input', function(){ el.setCustomValidity(''); }); }

      var elToken = q('token'), elEmail = q('email'), elField3 = q('field3'), elField4 = q('field4'), elCaptcha = q('captcha');
      [elToken, elEmail, elField3, elField4, elCaptcha].forEach(clearOnInput);

      if (elCaptcha) {
        elCaptcha.addEventListener('input', function(){ elCaptcha.value = elCaptcha.value.toUpperCase(); });
      }

      form.addEventListener('submit', function (e) {
        [elToken, elEmail, elField3, elField4, elCaptcha].forEach(function(el){ setErr(el, ''); });
        var token = (elToken && elToken.value ? elToken.value.trim() : '');
        var email = (elEmail && elEmail.value ? elEmail.value.trim() : '');
        var field3 = (elField3 && elField3.value ? elField3.value.trim() : '');
        var field4 = (elField4 && elField4.value ? elField4.value : '');
        var captcha = (elCaptcha && elCaptcha.value ? elCaptcha.value.trim() : '');
        var ok = true;

        if (!/^[A-Za-z0-9]{1,64}$/.test(token)) { setErr(elToken, 'Token 必填，只能半角英数字，长度不超过 64。'); ok = false; }
        if (email.length === 0) { setErr(elEmail, 'Email 必填。'); ok = false; }
        else if (email.length > 200) { setErr(elEmail, 'Email 长度不能超过 200。'); ok = false; }
        if (field3.length === 0) { setErr(elField3, '科目3 必填。'); ok = false; }
        else if (field3.length > 50) { setErr(elField3, '科目3 长度不能超过 50。'); ok = false; }
        else if (!/^[0-9A-Za-z\u4e00-\u9fa5]+$/.test(field3)) { setErr(elField3, '科目3 仅允许数字/英文/中文字符。'); ok = false; }
        if (field4.trim().length === 0) { setErr(elField4, '科目4 必填。'); ok = false; }
        else if (field4.length > 200) { setErr(elField4, '科目4 长度不能超过 200。'); ok = false; }
        if (!/^[A-Za-z0-9]{4}$/.test(captcha)) { setErr(elCaptcha, '验证码必填，且为 4 位字母/数字。'); ok = false; }

        if (!ok){
          e.preventDefault();
          var firstInvalid = form.querySelector(':invalid');
          if (firstInvalid) firstInvalid.reportValidity();
        }
      });
    })();
  </script>
</body>
</html><?php /**PATH F:\phpstudy81\WWW\fyjt\resources\views/fyjt-form.blade.php ENDPATH**/ ?>