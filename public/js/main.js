const dropArea   = document.getElementById('drop-area');
    const fileElem   = document.getElementById('fileElem');
    const fileSelect = document.getElementById('fileSelect');
    const progress   = document.getElementById('progress-bar');
    const result     = document.getElementById('result');

    ['dragenter','dragover','dragleave','drop']
      .forEach(e => dropArea.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }, false));

    dropArea.addEventListener('drop', e => {
      if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });

    fileSelect.addEventListener('click', () => fileElem.click());
    fileElem.addEventListener('change', () => uploadFile(fileElem.files[0]));

    function uploadFile(file) {
      progress.style.display = 'block';
      const xhr = new XMLHttpRequest();
      xhr.open('POST', '/upload');
      xhr.upload.onprogress = e => {
        if (e.lengthComputable) progress.value = (e.loaded / e.total) * 100;
      };
      xhr.onload = () => {
        progress.style.display = 'none';
        const translations = document.getElementById('translations');
        if (xhr.status === 200) {
          const res = JSON.parse(xhr.responseText);
          const uploadedLabel = translations.dataset.uploadedLabel;
          const separator = translations.dataset.separator || ': ';
          result.innerHTML = `<p>${uploadedLabel}${separator}<a href="${res.url}" target="_blank">${res.url}</a></p>`;
        } else {
          const errorRes = JSON.parse(xhr.responseText);
          const uploadFailedMsg = translations.dataset.uploadFailed;
          result.textContent = errorRes.error || uploadFailedMsg;
        }
      };
      const fd = new FormData();
      fd.append('file', file);
      const csrfToken = document.getElementById('csrfToken').value;
      fd.append('csrf_token', csrfToken);

      xhr.send(fd);
    }

    // Delete functionality
    const fileList = document.getElementById('file-list');
    if (fileList) {
        fileList.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-btn')) {
                const translations = document.getElementById('translations');
                const confirmMessage = translations.dataset.confirmDelete;
                if (!confirm(confirmMessage)) {
                    return;
                }
                const hash = e.target.dataset.hash;
                const csrfToken = document.getElementById('csrfToken').value;

                const response = await fetch('/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `hash=${hash}&csrf_token=${csrfToken}`
                });

                const result = await response.json();

                if (result.success) {
                    e.target.closest('li').remove();
                } else {
                    const translations = document.getElementById('translations');
                    const errorDeleting = translations.dataset.errorDeleting;
                    const separator = translations.dataset.separator || ': ';
                    alert(errorDeleting + separator + result.error);
                }
            }
        });
    }

    // Language switching functionality
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        // Set the correct selected option based on current locale
        const translations = document.getElementById('translations');
        const currentLocale = translations.dataset.currentLocale;
        if (currentLocale) {
            languageSelect.value = currentLocale;
        }

        languageSelect.addEventListener('change', function() {
            const selectedLang = this.value;
            // Set a cookie to remember the language preference
            document.cookie = `lang=${selectedLang}; path=/; max-age=31536000`; // 1 year
            // Reload the page to apply the new language
            window.location.reload();
        });
    }