document.addEventListener('DOMContentLoaded', function() {
  // Contador de caracteres
  const descricao = document.getElementById('descricao');
  const charCounter = document.getElementById('char-counter');
  const maxLength = 1000;
  
  if (descricao && charCounter) {
      atualizarContador();
      
      descricao.addEventListener('input', atualizarContador);
      
      function atualizarContador() {
          const comprimento = descricao.value.length;
          charCounter.textContent = `${comprimento}/${maxLength}`;
          
          if (comprimento > maxLength * 0.9) {
              charCounter.style.color = '#ff6b6b';
          } else {
              charCounter.style.color = '';
          }
      }
  }
  
  // Sistema de upload de imagens
  const fileInput = document.getElementById('imagens');
  const uploadArea = document.getElementById('uploadArea');
  const previewContainer = document.getElementById('previewContainer');
  const selectFilesBtn = document.getElementById('selectFilesBtn');
  const selectedCount = document.getElementById('selectedCount');
  const submitBtn = document.getElementById('submitBtn');
  const maxFiles = 4;
  const maxFileSize = 5 * 1024 * 1024; // 5MB
  
  let files = [];
  
  uploadArea.addEventListener('click', () => fileInput.click());
  selectFilesBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      fileInput.click();
  });
  
  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      uploadArea.addEventListener(eventName, preventDefaults, false);
  });
  
  function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
  }
  
  ['dragenter', 'dragover'].forEach(eventName => {
      uploadArea.addEventListener(eventName, highlight, false);
  });
  
  ['dragleave', 'drop'].forEach(eventName => {
      uploadArea.addEventListener(eventName, unhighlight, false);
  });
  
  function highlight() {
      uploadArea.classList.add('dragover');
  }
  
  function unhighlight() {
      uploadArea.classList.remove('dragover');
  }
  
  uploadArea.addEventListener('drop', handleDrop, false);
  
  function handleDrop(e) {
      const dt = e.dataTransfer;
      const droppedFiles = dt.files;
      handleFiles(droppedFiles);
  }
  
  fileInput.addEventListener('change', function() {
      handleFiles(this.files);
      this.value = '';
  });
  
  function handleFiles(newFiles) {
      const currentCount = files.length;
      const availableSlots = maxFiles - currentCount;
      
      if (availableSlots <= 0) {
          alert(`Você já selecionou o máximo de ${maxFiles} imagens. Remova algumas antes de adicionar novas.`);
          return;
      }
      
      const filesToAdd = Array.from(newFiles).slice(0, availableSlots);
      
      filesToAdd.forEach(file => {
          if (!file.type.match('image.*')) {
              alert(`O arquivo ${file.name} não é uma imagem válida.`);
              return;
          }
          
          if (file.size > maxFileSize) {
              alert(`O arquivo ${file.name} excede o limite de 5MB.`);
              return;
          }
          
          files.push(file);
          createPreview(file);
      });
      
      updateCounter();
      updateSubmitButton();
  }
  
  function createPreview(file) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
          const previewItem = document.createElement('div');
          previewItem.className = 'preview-item';
          
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'preview-image';
          img.alt = file.name;
          
          const removeBtn = document.createElement('button');
          removeBtn.className = 'remove-btn';
          removeBtn.innerHTML = '×';
          removeBtn.title = 'Remover imagem';
          
          removeBtn.addEventListener('click', function() {
              const index = Array.from(previewContainer.children).indexOf(previewItem);
              if (index !== -1) {
                  files.splice(index, 1);
                  previewItem.remove();
                  updateCounter();
                  updateSubmitButton();
              }
          });
          
          previewItem.appendChild(img);
          previewItem.appendChild(removeBtn);
          previewContainer.appendChild(previewItem);
      };
      
      reader.readAsDataURL(file);
  }
  
  function updateCounter() {
      selectedCount.textContent = files.length;
  }
  
  function updateSubmitButton() {
      submitBtn.disabled = false;
  }
  
  document.querySelector('form').addEventListener('submit', function(e) {
if (files.length === 0) {
  return;
}

const formData = new FormData(this);

formData.delete('imagens[]');

files.forEach((file) => {
  formData.append('imagens[]', file);
});

fetch(this.action, {
  method: 'POST',
  body: formData,
})
.then(response => {
  if (response.redirected) {
      window.location.href = response.url;
  } else {
      return response.text();
  }
})
.catch(error => console.error('Erro no envio:', error));

e.preventDefault();
});

});