_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 100);

    table = $("#tabelApproval").DataTable({
        ajax: "/persetujuanuser",
        processing: true,
        serverSide: true,
        responsive: true,
        // aaSorting: [[1, "asc"]],
        columns: [
            {
                data: "panggil",
                name: "panggil",
                orderable: true,
                searchable: true,
            },
            {
                data: "lowongan_apply.pelamar.nama",
            },
            {
                data: "usia",
                name: "usia",
                orderable: true,
                searchable: true,
            },
            {
                data: "lowongan_apply.pelamar.jenjang_pendidikan",
            },
            {
                data: "penempatan",
                name: "penempatan",
                orderable: true,
                searchable: true,
            },
            {
                data: "ceklis",
                name: "ceklis",
                orderable: false,
                searchable: false,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center",
            },
            {
                targets: [0, 2, 3, 4, 5],
                className: "dt-center",
            }
        ],
    });

    // Array global untuk menampung semua ID yang dipilih
    let selectedIds = [];

    $(document).on('change', '.check-user', function () {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            if (!selectedIds.includes(id)) selectedIds.push(id);
        } else {
            selectedIds = selectedIds.filter(item => item !== id);
        }
        // update jumlah ke Blade
        $('#totalDipilih').text(selectedIds.length);

    });

    // cek dan checked di page berbeda apabila sudah di checked sebelumnya
    $('#pelamarUser').on('draw.dt', function () {
        $('.check-user').each(function () {
            const id = $(this).val();
            $(this).prop('checked', selectedIds.includes(id));
        });

    });

    document.getElementById("formApproval").addEventListener("submit", function (e) {

        // Tambahkan hidden input ke form
        selectedIds.forEach(function (id) {
            $('<input>').attr({
                type: 'hidden',
                name: 'noreg[]',
                value: id
            }).appendTo('form');
        });

        e.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            html: "Sebanyak " + selectedIds.length + " pelamar akan diproses.<br>Pelamar yang tidak dipilih akan dikembalikan lagi ke data pelamar.",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Proses",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/persetujuanuser",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        noreg: selectedIds,
                    },
                    success: function (response) {
                        $.unblockUI();
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1200,
                            allowOutsideClick: false,
                            showClass: {
                                popup: "animate__animated animate__rubberBand",
                            },
                            hideClass: {
                                popup: "animate__animated animate__bounceOut",
                            },
                            customClass: {
                                confirmButton: "",
                            },
                            buttonsStyling: false,
                            willOpen: function () {
                                Swal.showLoading();
                            },
                        }).then(function (result) {
                            table.ajax.reload(null, false);
                            $('#totalDipilih').text(0);
                        });
                    },
                    error: function (error) {
                        $.unblockUI();
                        let errorMessage = error.statusText;
                        let errorTitle = `Error ${error.status}`;

                        // Coba parse JSON jika ada responseText
                        if (error.responseText) {
                            try {
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    // errorMessage = Object.values(response.errors)
                                    //     .flat()
                                    //     .join('\n');
                                    var errors = $.parseJSON(error.responseText);
                                    errorMessage = errors.errors;
                                } else if (response.message) {
                                    // Jika ada message langsung
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // Jika gagal parse, pakai responseText mentah
                                // errorMessage = error.responseText;
                                errorMessage = error.statusText;
                            }
                        }

                        Swal.fire({
                            title: errorTitle,
                            text: errorMessage,
                            icon: "error",
                            showClass: {
                                popup: "animate__animated animate__shakeX",
                            },
                            hideClass: {
                                popup: "animate__animated animate__bounceOut",
                            },
                            customClass: {
                                confirmButton:
                                    "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    }
                });
            }
        });

    });

    $(document).on('click', '.approvalOne', function () {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        Swal.fire({
            title: "Konfirmasi!",
            html: nama + " <br>Akan diproses menjadi karyawan.",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Proses",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/persetujuanuser-one",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        id: id,
                    },
                    success: function (response) {
                        $.unblockUI();
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1200,
                            allowOutsideClick: false,
                            showClass: {
                                popup: "animate__animated animate__rubberBand",
                            },
                            hideClass: {
                                popup: "animate__animated animate__bounceOut",
                            },
                            customClass: {
                                confirmButton: "",
                            },
                            buttonsStyling: false,
                            willOpen: function () {
                                Swal.showLoading();
                            },
                        }).then(function (result) {
                            table.ajax.reload(null, false);
                            $('#totalDipilih').text(0);
                        });
                    },
                    error: function (error) {
                        $.unblockUI();
                        let errorMessage = error.statusText;
                        let errorTitle = `Error ${error.status}`;

                        // Coba parse JSON jika ada responseText
                        if (error.responseText) {
                            try {
                                let response = JSON.parse(error.responseText);
                                // Jika format Laravel: { errors: { field: ["msg1", "msg2"] } }
                                if (response.errors) {
                                    // Gabungkan semua pesan error jadi satu string
                                    errorMessage = Object.values(response.errors)
                                        .flat()
                                        .join('\n');
                                } else if (response.message) {
                                    // Jika ada message langsung
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // Jika gagal parse, pakai responseText mentah
                                // errorMessage = error.responseText;
                                errorMessage = error.statusText;
                            }
                        }

                        Swal.fire({
                            title: errorTitle,
                            text: errorMessage,
                            icon: "error",
                            showClass: {
                                popup: "animate__animated animate__shakeX",
                            },
                            hideClass: {
                                popup: "animate__animated animate__bounceOut",
                            },
                            customClass: {
                                confirmButton:
                                    "btn btn-danger waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    },
                });
            }
        });
    })
})