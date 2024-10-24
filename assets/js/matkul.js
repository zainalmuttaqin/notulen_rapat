var deleteForm = document.querySelectorAll(".formDelete");

deleteForm.forEach((form) => {
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    var currForm = this;
    var nama = this.getAttribute("nama-matkul");
    Swal.fire({
      title: "Anda yakin?",
      html: `Mata kuliah <b>${nama}</b> akan dihapus!`,
      icon: "question",
      confirmButtonText: "Ya!",
      showCancelButton: true,
      cancelButtonText: "Tidak!",
    }).then((response) => {
      if (response.isConfirmed) {
        currForm.submit();
      }
    });
  });
});
