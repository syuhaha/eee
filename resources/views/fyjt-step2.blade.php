<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>fyjt — 第二步</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{ --bg:#f6f8ff; --card:#fff; --muted:#6b7280; --text:#0f172a; --accent:#4f46e5; --border:rgba(15,23,42,0.06); --radius:12px; --font:'DM Sans',system-ui,-apple-system, sans-serif }
    *{box-sizing:border-box}
    body{margin:0;font-family:var(--font);background:linear-gradient(180deg,#f6f8ff,#eef2ff);padding:36px 16px;display:flex;justify-content:center}
    .wrap{width:100%;max-width:720px;margin:0 auto;display:block}
    .card{width:100%;background:var(--card);border-radius:var(--radius);padding:22px;border:1px solid var(--border);box-shadow:0 8px 24px rgba(15,23,42,0.06)}
    .header{display:flex;gap:12px;align-items:center;margin-bottom:8px}
    .logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#06b6d4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    h1{margin:0;font-size:18px}
    .subtitle{color:var(--muted);font-size:13px}
    .disclaimer{font-size:13px;color:var(--muted);background:linear-gradient(180deg,rgba(15,23,42,0.01),transparent);border-radius:10px;padding:12px;border:1px dashed rgba(15,23,42,0.04);margin:12px 0}
    form{display:flex;flex-direction:column;gap:30px}
    label{font-weight:600}
    input,textarea{padding:10px 12px;border-radius:8px;border:1px solid var(--border);width:100%;box-sizing:border-box}
    textarea{min-height:120px}
    .btn{background:linear-gradient(90deg,var(--accent),#06b6d4);color:#fff;border:none;padding:10px 14px;border-radius:10px;cursor:pointer}
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
          <h1>fyjt — 第二步</h1>
          <div class="subtitle">请补充剩余信息并提交。</div>
        </div>
      </div>

      @if(session('success'))
        <div style="color:#059669;background:#ecfdf5;padding:10px;border-radius:8px">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="err">请修正以下错误并重试。</div>
      @endif

      <form method="POST" action="{{ route('fyjt.submit') }}" id="fyjtStep2" novalidate>
        @csrf

        {{-- token 放入隐藏域，值来自第1步保存在 session 中 --}}
        <input type="hidden" name="token" value="{{ session('fyjt_valid_token', old('token')) }}">

        <div>
          <label for="email">bbb（Email）</label>
          <input id="email" name="email" type="email" maxlength="200" autocomplete="email" value="{{ old('email') }}" required placeholder="name@example.com">
          @error('email') <div class="err">{{ $message }}</div> @enderror
          <div class="hint">Email 必填，最长 200。</div>
        </div>

        <div>
          <label for="field3">ccc&nbsp;&nbsp;<span class="hint" id="field3Count">0 / 50</span></label>
          <input id="field3" name="field3" type="text" maxlength="50" autocomplete="off" value="{{ old('field3') }}" required placeholder="科目3（数字/英文/中文）">
          @error('field3') <div class="err">{{ $message }}</div> @enderror
          <div class="hint">仅允许数字/英文/中文字符，长度不超过 50。</div>
        </div>

        <div>
          <label for="field4">ddd&nbsp;&nbsp;<span class="hint" id="field4Count">0 / 200</span></label>
          <textarea id="field4" name="field4" maxlength="200" required placeholder="请输入内容（不超过200字）">{{ old('field4') }}</textarea>
          @error('field4') <div class="err">{{ $message }}</div> @enderror
          <div class="hint">必填，长度不超过 200。</div>
        </div>

        <div style="text-align:center;margin-top:6px">
          <button class="btn" type="submit">提交</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      function updateCount(id,outId,max){var el=document.getElementById(id),out=document.getElementById(outId); if(!el||!out)return; function fn(){out.textContent=el.value.length+' / '+max;} el.addEventListener('input',fn); fn();}
      updateCount('field3','field3Count',50); updateCount('field4','field4Count',200);

      // 与原先的客户端校验保持一致（体验层）
      var form=document.getElementById('fyjtStep2'); if(!form)return;
      function q(name){return form.querySelector('[name="'+name+'"]');}
      function setErr(el,msg){ if(!el) return; el.setCustomValidity(msg||''); }
      function clearOnInput(el){ if(!el) return; el.addEventListener('input',function(){el.setCustomValidity('');}); }
      var elEmail=q('email'), elField3=q('field3'), elField4=q('field4'); [elEmail,elField3,elField4].forEach(clearOnInput);

      // Email onblur 验证：trim 后若有内容则校验格式；为空则不做必填提示（提交时再检查）
      if (elEmail) {
        elEmail.addEventListener('blur', function(){
          var v = (elEmail.value||'').trim();
          elEmail.value = v; // 写回已 trim 的值
          if (v.length === 0) { elEmail.setCustomValidity(''); return; }
          var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRe.test(v)) {
            elEmail.setCustomValidity('Email 格式不正确。');
            // 在 blur 时主动显示校验提示
            try { elEmail.reportValidity(); } catch (e) {}
          }
          else { elEmail.setCustomValidity(''); }
        });
      }
      form.addEventListener('submit',function(e){
        // 先将输入左右两边 trim 并写回对应元素，然后再做校验
        [elEmail, elField3, elField4].forEach(function(el){ if(!el) return; if(typeof el.value === 'string'){ var tv = el.value.trim(); if(el.value !== tv) el.value = tv; } });

        [elEmail,elField3,elField4].forEach(function(el){ setErr(el,''); });
        var ok=true;
        var email=(elEmail&&elEmail.value?elEmail.value.trim():'');
        var field3=(elField3&&elField3.value?elField3.value.trim():'');
        var field4=(elField4&&elField4.value?elField4.value.trim():'');
        if(email.length===0){ setErr(elEmail,'Email 必填。'); ok=false; } else if(email.length>200){ setErr(elEmail,'Email 长度不能超过 200。'); ok=false; }
        if(field3.length===0){ setErr(elField3,'科目3 必填。'); ok=false; } else if(field3.length>50){ setErr(elField3,'科目3 长度不能超过 50。'); ok=false; } else if(!/^[0-9A-Za-z\u4e00-\u9fa5]+$/.test(field3)){ setErr(elField3,'科目3 仅允许数字/英文/中文字符。'); ok=false; }
        if(field4.length===0){ setErr(elField4,'科目4 必填。'); ok=false; } else if(field4.length>200){ setErr(elField4,'科目4 长度不能超过 200。'); ok=false; }
        if(!ok){ e.preventDefault(); var firstInvalid=form.querySelector(':invalid'); if(firstInvalid) firstInvalid.reportValidity(); }
      });
    })();
  </script>
</body>
</html>
