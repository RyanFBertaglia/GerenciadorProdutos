document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imagem');
    const fileList = document.getElementById('file-list');
    const uploadLabel = document.querySelector('.file-upload-label');
    const submitButton = document.getElementById('submitButton');
    const fileLimit = 4;
    const maxSize = 5 * 1024 * 1024; // 5MB

    showEmptyMessage();

    uploadLabel.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', updateFileList);

    function updateFileList() {
        fileList.innerHTML = '';
        
        if (!fileInput.files.length) {
            showEmptyMessage();
            return;
        }

        if (fileInput.files.length > fileLimit) {
            alert(`Você pode enviar no máximo ${fileLimit} imagens.`);
            fileInput.value = '';
            showEmptyMessage();
            return;
        }

        Array.from(fileInput.files).forEach((file, index) => {
            if (file.size > maxSize) {
                alert(`O arquivo "${file.name}" excede o limite de 5MB.`);
                fileInput.value = '';
                showEmptyMessage();
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert(`O arquivo "${file.name}" não é um tipo de imagem válido.`);
                fileInput.value = '';
                showEmptyMessage();
                return;
            }

            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            fileItem.innerHTML = `
                <span class="file-icon">📄</span>
                <div class="file-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                </div>
                <span class="file-remove" data-index="${index}">×</span>
            `;
            
            fileList.appendChild(fileItem);
        });

        document.querySelectorAll('.file-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFile(parseInt(this.getAttribute('data-index')));
            });
        });
        updateCounter();
    }

    function removeFile(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== index) dt.items.add(files[i]);
        }
        
        fileInput.files = dt.files;
        updateFileList();
    }

    function showEmptyMessage() {
        fileList.innerHTML = '<div class="empty-message">Nenhuma imagem selecionada</div>';
        updateCounter();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateCounter() {
        const count = fileInput.files ? fileInput.files.length : 0;
        uploadLabel.textContent = `Adicionar Imagens (${count}/${fileLimit})`;
    }
});