<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>fyjt</title>
  <style>
    :root { --bg:#f6f7fb; --card:#fff; --bd:#e5e7eb; --txt:#111827; --muted:#6b7280; --err:#b91c1c; --ok:#065f46; --btn:#2563eb; }
    body{ margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial; background:var(--bg); color:var(--txt); }
    .wrap{ max-width:920px; margin:0 auto; padding:18px; }
    .card{ background:var(--card); border:1px solid var(--bd); border-radius:12px; padding:18px; }
    .title{ font-size:20px; font-weight:700; margin:0 0 12px; }
    .grid{ display:grid; grid-template-columns: 1fr; gap:14px; }
    label{ display:block; font-weight:600; margin:0 0 6px; }
    input, textarea{ width:100%; box-sizing:border-box; border:1px solid var(--bd); border-radius:10px; padding:10px 12px; font-size:14px; background:#fff; }
    textarea{ min-height:110px; resize:vertical; }
    .hint{ font-size:12px; color:var(--muted); margin-top:6px; }
    .err{ font-size:12px; color:var(--err); margin-top:6px; }
    .bar{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .btn{ background:var(--btn); color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:700; cursor:pointer; }
    .msg{ padding:10px 12px; border-radius:10px; margin-bottom:12px; border:1px solid var(--bd); background:#fff; }
    .msg.ok{ border-color:#a7f3d0; background:#ecfdf5; color:var(--ok); }
    .msg.bad{ border-color:#fecaca; background:#fef2f2; color:var(--err); }
    .disclaimer{ font-size:13px; color:var(--muted); line-height:1.6; background:#fafafa; border:1px dashed var(--bd); padding:12px; border-radius:10px; }
    .captcha{ display:flex; gap:10px; align-items:center; }
    .captcha img{ height:44px; border:1px solid var(--bd); border-radius:10px; cursor:pointer; }
    .counter{ font-variant-numeric: tabular-nums; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1 class="title">fyjt</h1>

      @if(session('success'))
        <div class="msg ok">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="msg bad">请检查表单输入后重试。</div>
      @endif

      <div class="disclaimer">
        <strong>科目1（免责文字）</strong><br>
        本页面用于提交信息。你确认所填内容真实有效；因填写信息错误、第三方不可用等导致的结果由提交方自行承担。
      </div>

      <form method="POST" action="{{ route('fyjt.submit') }}" style="margin-top:14px;">
        @csrf
        <div>
                  <label>Token（）</label>
                  <input type="text" name="token" value="{{ old('token') }}" maxlength="64" required>
                  <div class="hint">&nbsp;</div>
                  @error('token') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="grid">
          <div>
            <label>Target email address（）</label>
            <input type="text" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
            <div class="hint">&nbsp;</div>
            @error('email') <div class="err">{{ $message }}</div> @enderror
          </div>

          <div>
            <label>Title（）</label>
            <input type="text" id="field3" name="field3" value="{{ old('field3') }}" maxlength="50" required>
            <div class="hint counter" id="field3Count">0 / 50</div>
            @error('field3') <div class="err">{{ $message }}</div> @enderror
          </div>

          <div style="grid-column: 1 / -1;">
            <label>Content（）</label>
            <textarea id="field4" name="field4" maxlength="200" required>{{ old('field4') }}</textarea>
            <div class="hint counter" id="field4Count">0 / 200</div>
            @error('field4') <div class="err">{{ $message }}</div> @enderror
          </div>

          <div style="grid-column: 1 / -1;">
            <label>CAPTCHA（验证码）</label>
            <div class="captcha">
              <input type="text" name="captcha" maxlength="4" placeholder="" required style="max-width:220px;">
              <img id="captchaImg" src="{{ route('fyjt.captcha') }}?t={{ time() }}" alt="captcha" title="click refresh">
              <button type="button" class="btn" id="refreshBtn">refresh</button>
            </div>
            <div class="hint">点击验证码图片或“刷新”可更换验证码。</div>
            @error('captcha') <div class="err">{{ $message }}</div> @enderror
          </div>
        </div>

        <div style="margin-top:14px; text-align:center;">
          <button class="btn" type="submit">Submit</button>
        </div>
      </form>
    </div>
  </div>

<script>
  function updateCount(id, outId, max){
    const el = document.getElementById(id);
    const out = document.getElementById(outId);
    const fn = () => out.textContent = (el.value.length) + " / " + max;
    el.addEventListener('input', fn);
    fn();
  }
  updateCount('field3','field3Count',50);
  updateCount('field4','field4Count',200);

  function refreshCaptcha(){
    const img = document.getElementById('captchaImg');
    img.src = "{{ route('fyjt.captcha') }}?t=" + Date.now();
  }
  document.getElementById('captchaImg').addEventListener('click', refreshCaptcha);
  document.getElementById('refreshBtn').addEventListener('click', refreshCaptcha);
</script>
</body>
</html>