(function () {
  'use strict';
  var form = document.getElementById('chatForm');
  if (!form) return;
  var log = document.getElementById('chatLog');
  var input = document.getElementById('question');
  var btn = document.getElementById('sendBtn');
  var modeSelect = document.getElementById('mode');
  var err = document.getElementById('chatError');
  var metaToken = document.querySelector('meta[name="csrf-token"]');
  var token = metaToken ? metaToken.content : '';

  if (log) log.scrollTop = log.scrollHeight;

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function autoResizeInput() {
    if (!input) return;
    input.style.height = 'auto';
    var newH = Math.min(input.scrollHeight, 180);
    input.style.height = (newH > 42 ? newH : 42) + 'px';
  }

  if (input) {
    input.addEventListener('input', autoResizeInput);
  }

  // Gợi ý câu hỏi nhanh (Prompt chips)
  document.querySelectorAll('.chat-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      if (!input || !form) return;
      input.value = chip.dataset.prompt || chip.textContent.trim();
      if (chip.dataset.mode && modeSelect) {
        modeSelect.value = chip.dataset.mode;
      }
      autoResizeInput();
      input.focus();
    });
  });

  function bubble(cls, html) {
    var d = document.createElement('div');
    d.className = 'msg ' + cls;
    d.innerHTML = html;
    log.appendChild(d);
    log.scrollTop = log.scrollHeight;
    return d;
  }

  function showError(m) {
    err.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> ' + esc(m);
    err.classList.remove('d-none');
    log.scrollTop = log.scrollHeight;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var q = input.value.trim();
    if (q.length < 2) return;
    err.classList.add('d-none');

    var empty = document.getElementById('chatEmpty');
    if (empty) empty.remove();

    bubble('user', esc(q).replace(/\n/g, '<br>'));

    // Trạng thái đang xử lý (skeleton shimmer)
    var wait = bubble('assistant', '<div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-stars text-primary"></i> <span class="small text-muted fw-semibold">AI đang suy nghĩ và đối chiếu tài liệu…</span></div><span class="skeleton"></span><span class="skeleton" style="width:75%"></span><span class="visually-hidden">Đang xử lý…</span>');

    btn.disabled = true;
    input.disabled = true;

    var ctrl = new AbortController();
    var to = setTimeout(function () { ctrl.abort(); }, 90000);

    fetch(form.dataset.url, {
      method: 'POST',
      signal: ctrl.signal,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token
      },
      body: JSON.stringify({ question: q, mode: modeSelect ? modeSelect.value : 'ask' })
    })
      .then(function (r) {
        return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; });
      })
      .then(function (res) {
        wait.remove();
        if (!res.ok) {
          var m = res.body && res.body.message
            ? res.body.message
            : (res.body && res.body.errors ? Object.values(res.body.errors)[0][0] : 'Có lỗi xảy ra (HTTP ' + res.status + ').');
          showError(m);
          return;
        }

        var a = res.body.assistant;
        var html = '<div class="ai-response-content">' + a.html + '</div>';

        if (a.sources && a.sources.length) {
          html += '<div class="sources"><div class="fw-semibold text-muted small mb-1"><i class="bi bi-journal-check text-primary me-1"></i> Nguồn tài liệu đối chiếu:</div>';
          a.sources.forEach(function (s) {
            html += '<div class="src">' +
              '<div class="d-flex justify-content-between align-items-start gap-2 mb-1">' +
                '<span><span class="ref-tag">[' + esc(s.ref) + ']</span> <strong class="text-dark">' + esc(s.title) + '</strong>' +
                (s.page ? ' <span class="badge text-bg-light border ms-1">Trang ' + esc(s.page) + '</span>' : '') + '</span>' +
                (s.url ? '<a class="btn btn-xs btn-outline-primary py-0 px-2 small text-nowrap" target="_blank" rel="noopener" href="' + esc(s.url) + '"><i class="bi bi-box-arrow-up-right"></i> Xem gốc</a>' : '') +
              '</div>' +
              '<div class="text-muted small fst-italic ps-2 border-start border-2">“' + esc(s.excerpt) + '”</div>' +
            '</div>';
          });
          html += '</div>';
        }

        bubble('assistant' + (a.status === 'failed' ? ' failed' : ''), html);
        input.value = '';
        autoResizeInput();
      })
      .catch(function (ex) {
        wait.remove();
        showError(ex.name === 'AbortError' ? 'Quá thời gian chờ phản hồi (90 giây). Vui lòng thử lại.' : 'Không thể kết nối máy chủ.');
      })
      .finally(function () {
        clearTimeout(to);
        btn.disabled = false;
        input.disabled = false;
        input.focus();
      });
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form.requestSubmit();
    }
  });
})();
