function toggleButton () {
    var btnCancel = document.querySelector("#btn-batal");
    var btnUbah = document.querySelector("#btn-ubah");
    var btnSimpan = document.querySelector("#btn-simpan");
    btnCancel.classList.toggle("hidden");
    btnUbah.classList.toggle("hidden");
    btnSimpan.classList.toggle("hidden");
  }
  
  function handlePreview (e) {
    var preview = document.querySelector("#previewFoto");
    var [files] = e.files;
    if (files) {
      preview.src = URL.createObjectURL(files);
    }
    toggleButton();
  }
  
  function handleCancel(def) {
    var preview = document.querySelector("#previewFoto");
    var input = document.querySelector("#foto");
    input.type = 'text'
    input.value = "";
    input.type = 'file';
    preview.src = def;
    toggleButton();
  }