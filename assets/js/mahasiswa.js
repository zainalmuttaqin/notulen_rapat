var changeStatusForm = document.querySelectorAll(".formChangeStatus");

changeStatusForm.forEach((form) => {
  form.addEventListener("submit", function (e) {
    e.preventDefault()
    var currForm = this;
    var nama = this.getAttribute("nama-mahasiswa");
    var status = this.getAttribute("status");
    Swal.fire({
      title: "Anda yakin?",
      html: `Mahasiswa <b>${nama}</b> akan ${status == '1' ? 'dinonaktifkan' : 'diaktifkan'}`,
      icon: "question",
      confirmButtonText: "Ya!",
      showCancelButton: true,
      cancelButtonText: "Tidak!",
    }).then((response) => {
      if (response.isConfirmed) {
        currForm.submit();
      }
    });
})
});

var deleteForm = document.querySelectorAll('.formDelete');

deleteForm.forEach((form) => {
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    var currForm = this;
    var nama = this.getAttribute("nama-mahasiswa");
    Swal.fire({
      title: "Anda yakin?",
      html: `Mahasiswa <b>${nama}</b> akan dihapus!`,
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
