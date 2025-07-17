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
        if (xhr.status === 200) {
          const res = JSON.parse(xhr.responseText);
          result.innerHTML = `<p>Uploaded: <a href="${res.url}" target="_blank">${res.url}</a></p>`;
        } else {
          result.textContent = 'Upload failed';
        }
      };
      const fd = new FormData();
      fd.append('file', file);
      const csrfToken = document.getElementById('csrfToken').value;
      fd.append('csrf_token', csrfToken);

      xhr.send(fd);
    }

    // Delete functionality
    document.getElementById('file-list').addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-btn')) {
            if (!confirm('Are you sure you want to delete this file?')) {
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
                alert('Error deleting file: ' + result.error);
            }
        }
    });