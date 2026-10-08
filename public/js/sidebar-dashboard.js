document.getElementById("logout-link")?.addEventListener("click", function (e) {
    e.preventDefault();
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Anda akan keluar dari sistem ini.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Keluar",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("logout-form").submit();
        }
    });
});
