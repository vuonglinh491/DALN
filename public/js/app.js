(function () {
  'use strict';

  // 1. Gợi ý tìm kiếm (Fetch API + Keyboard Navigation)
  var input = document.getElementById('navSearch');
  var box = document.getElementById('suggestBox');
  if (input && box) {
    var timer = null, ctrl = null, activeIdx = -1;

    function renderActive() {
      var items = box.querySelectorAll('.list-group-item');
      items.forEach(function (el, idx) {
        if (idx === activeIdx) {
          el.classList.add('active');
          el.scrollIntoView({ block: 'nearest' });
        } else {
          el.classList.remove('active');
        }
      });
    }

    input.addEventListener('input', function () {
      clearTimeout(timer);
      activeIdx = -1;
      var q = input.value.trim();
      if (q.length < 2) { box.classList.add('d-none'); return; }
      timer = setTimeout(function () {
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        fetch(input.dataset.suggestUrl + '?q=' + encodeURIComponent(q), {
          signal: ctrl.signal,
          headers: { 'Accept': 'application/json' }
        })
          .then(function (r) { return r.ok ? r.json() : []; })
          .then(function (items) {
            box.innerHTML = '';
            activeIdx = -1;
            if (!items.length) { box.classList.add('d-none'); return; }
            items.forEach(function (it) {
              var li = document.createElement('li');
              li.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
              li.setAttribute('role', 'option');

              var icon = document.createElement('i');
              icon.className = 'bi bi-file-earmark-text text-primary';
              li.appendChild(icon);

              var a = document.createElement('a');
              a.href = it.url;
              a.className = 'text-decoration-none text-reset stretched-link flex-grow-1 text-truncate';
              a.textContent = it.title;
              li.appendChild(a);

              box.appendChild(li);
            });
            box.classList.remove('d-none');
          }).catch(function () {});
      }, 200);
    });

    input.addEventListener('keydown', function (e) {
      var items = box.querySelectorAll('.list-group-item');
      if (box.classList.contains('d-none') || !items.length) {
        if (e.key === 'Escape') box.classList.add('d-none');
        return;
      }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIdx = (activeIdx + 1) % items.length;
        renderActive();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIdx = (activeIdx - 1 + items.length) % items.length;
        renderActive();
      } else if (e.key === 'Enter' && activeIdx >= 0) {
        var activeLink = items[activeIdx].querySelector('a');
        if (activeLink) {
          e.preventDefault();
          window.location.href = activeLink.href;
        }
      } else if (e.key === 'Escape') {
        box.classList.add('d-none');
      }
    });

    document.addEventListener('click', function (e) {
      if (!box.contains(e.target) && e.target !== input) {
        box.classList.add('d-none');
      }
    });
  }

  // 2. Hộp thoại xác nhận thao tác nguy hiểm <form data-confirm="Nội dung">
  var modalEl = document.getElementById('confirmModal');
  if (modalEl && window.bootstrap) {
    var modal = new bootstrap.Modal(modalEl);
    var pendingForm = null;
    var lastSubmitter = null;

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('button[type=submit]');
      if (btn) lastSubmitter = btn;
    });

    document.addEventListener('submit', function (e) {
      var f = e.target;
      if (f.dataset && f.dataset.confirm && !f.dataset.confirmed) {
        e.preventDefault();
        pendingForm = f;
        var body = document.getElementById('confirmBody');
        if (body) body.textContent = f.dataset.confirm;
        var title = document.getElementById('confirmTitle');
        if (title && f.dataset.confirmTitle) title.textContent = f.dataset.confirmTitle;
        modal.show();
      }
    });

    var okBtn = document.getElementById('confirmOk');
    if (okBtn) {
      okBtn.addEventListener('click', function () {
        if (pendingForm) {
          pendingForm.dataset.confirmed = '1';
          modal.hide();
          if (lastSubmitter && lastSubmitter.name && !pendingForm.querySelector('input[name="' + lastSubmitter.name + '"]')) {
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = lastSubmitter.name;
            hidden.value = lastSubmitter.value;
            pendingForm.appendChild(hidden);
          }
          pendingForm.submit();
        }
      });
    }
  }

  // 3. Chống bấm đúp an toàn (Chỉ khóa khi form hợp lệ và cho phép khôi phục khi back/forward)
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (e.defaultPrevented || (f.dataset && f.dataset.noLock)) return;
    if (f.checkValidity && !f.checkValidity()) return;

    var submitBtns = f.querySelectorAll('button[type=submit]');
    submitBtns.forEach(function (b) {
      setTimeout(function () {
        if (!b.dataset.originalHtml) b.dataset.originalHtml = b.innerHTML;
        b.disabled = true;
      }, 0);
    });
  });

  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      document.querySelectorAll('button[type=submit]:disabled').forEach(function (b) {
        b.disabled = false;
        if (b.dataset.originalHtml) b.innerHTML = b.dataset.originalHtml;
      });
    }
  });

  // 4. File Dropzone tương tác trực quan
  var dropzone = document.querySelector('.file-dropzone');
  var fileInput = document.getElementById('f-file');
  if (dropzone && fileInput) {
    var promptEl = dropzone.querySelector('.dropzone-prompt');
    var selectedEl = dropzone.querySelector('.dropzone-selected');
    var fileNameEl = dropzone.querySelector('.dropzone-filename');
    var fileSizeEl = dropzone.querySelector('.dropzone-filesize');

    function updateFileInfo() {
      if (fileInput.files && fileInput.files.length > 0) {
        var f = fileInput.files[0];
        if (fileNameEl) fileNameEl.textContent = f.name;
        if (fileSizeEl) {
          var sz = f.size > 1048576 ? (f.size / 1048576).toFixed(2) + ' MB' : (f.size / 1024).toFixed(0) + ' KB';
          fileSizeEl.textContent = sz;
        }
        if (promptEl) promptEl.classList.add('d-none');
        if (selectedEl) selectedEl.classList.remove('d-none');
      } else {
        if (promptEl) promptEl.classList.remove('d-none');
        if (selectedEl) selectedEl.classList.add('d-none');
      }
    }

    fileInput.addEventListener('change', updateFileInfo);

    ['dragenter', 'dragover'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
      });
    });

    dropzone.addEventListener('drop', function (e) {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo();
      }
    });
  }

  // 5. Đánh giá sao tương tác (Star Rating)
  var starWidget = document.getElementById('interactiveStars');
  var scoreSelect = document.getElementById('score');
  if (starWidget && scoreSelect) {
    var stars = starWidget.querySelectorAll('.star-item');
    function highlightStars(val) {
      stars.forEach(function (s, idx) {
        if (idx < val) {
          s.classList.remove('bi-star', 'text-muted');
          s.classList.add('bi-star-fill', 'text-warning');
        } else {
          s.classList.remove('bi-star-fill', 'text-warning');
          s.classList.add('bi-star', 'text-muted');
        }
      });
    }

    highlightStars(parseInt(scoreSelect.value, 10) || 5);

    stars.forEach(function (s) {
      s.addEventListener('mouseenter', function () {
        var v = parseInt(s.dataset.val, 10);
        highlightStars(v);
      });
      s.addEventListener('click', function () {
        var v = parseInt(s.dataset.val, 10);
        scoreSelect.value = v;
        highlightStars(v);
      });
    });

    starWidget.addEventListener('mouseleave', function () {
      highlightStars(parseInt(scoreSelect.value, 10) || 5);
    });

    scoreSelect.addEventListener('change', function () {
      highlightStars(parseInt(scoreSelect.value, 10) || 5);
    });
  }

  // 6. Sao chép nội dung bản xem trước
  var copyBtn = document.getElementById('copyPreviewBtn');
  var previewBox = document.querySelector('.preview-box');
  if (copyBtn && previewBox) {
    copyBtn.addEventListener('click', function () {
      var text = previewBox.innerText;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function () {
          var old = copyBtn.innerHTML;
          copyBtn.innerHTML = '<i class="bi bi-check2"></i> Đã sao chép';
          setTimeout(function () { copyBtn.innerHTML = old; }, 2000);
        });
      }
    });
  }
})();
