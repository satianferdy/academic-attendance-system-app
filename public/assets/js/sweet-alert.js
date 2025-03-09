$(document).ready(function () {
    // Buat konfigurasi mixin untuk SweetAlert
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    // Ambil nilai dari data-attributes yang sudah kita set di body
    const sessionSuccess = $("body").data("session-success");
    const sessionError = $("body").data("session-error");

    // Jika ada session 'success', tampilkan SweetAlert dengan icon success
    if (sessionSuccess) {
        Toast.fire({
            icon: "success",
            title: sessionSuccess,
        });
    }

    // Jika ada session 'error', tampilkan SweetAlert dengan icon error
    if (sessionError) {
        Toast.fire({
            icon: "error",
            title: sessionError,
        });
    }

    // Tambahkan event listener pada tombol delete
    $(".btn-delete").on("click", function (e) {
        e.preventDefault(); // Mencegah aksi default tombol

        const form = $(this).closest("form"); // Mengambil form yang terkait dengan tombol delete

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user menekan tombol "Ya, hapus", submit form
                form.submit();
            }
        });
    });
});
