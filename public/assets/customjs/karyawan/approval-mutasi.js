_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    setTimeout(function () {
        $.unblockUI();
    }, 300);

    table = $('#dataTable').DataTable({
        ajax: '',
        processing: true,
        serverSide: true,
        // aaSorting: [[0, "asc"]],
        drawCallback: function () {
            $('table.dataTable tbody td').css('vertical-align', 'top');
        },

        columns: [
            {
                data: "status",
                name: "status",
                orderable: false,
                searchable: true,
            },
            {
                data: 'karyawan.nip',
            },
            {
                data: 'karyawan.nama',
            },
            {
                data: 'sebelumnya',
                name: "sebelumnya",
                orderable: true,
                searchable: false,
            },
            {
                data: 'tujuan',
                name: "tujuan",
                orderable: true,
                searchable: false,
            },
            {
                data: 'tglefektif',
                name: "tglefektif",
                orderable: true,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
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
                targets: [0, 1, 5, 6],
                className: "text-center",
            },


        ],
        // responsive: true,
    })

    $(document).on('click', '.Detail', function () {
        var token = $(this).data('token');
        _loading();
        $.getJSON('/detail-mutasikaryawan/?token=' + token, function (data) {
            $('#modalDetail .modal-title').html(data.karyawan.nip + ' | ' + data.karyawan.nama)

            var lama = data.project_old;
            let htmllama = lama.map((item, index) => {
                // contoh: warna bergantian merah, biru, hijau
                let colors = ['bg-secondary', 'bg-secondary', 'bg-secondary'];
                return `<span class="badge ${colors[index % colors.length]} text-black mt-1">${item}</span>`;
            }).join("<br>");

            $('#idmutasi').val(data.id);
            $('#projectlama').html(htmllama);

            var baru = data.project_old;
            let htmlbaru = baru.map((item, index) => {
                // contoh: warna bergantian merah, biru, hijau
                let colors = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
                return `<span class="badge ${colors[index % colors.length]} text-black mt-1">${item}</span>`;
            }).join("<br>");

            $('#projectbaru').html(htmlbaru);
            $('#tglmulai').html('Tanggal Mulai Efektif : ' + data.tanggal);
            $('#keterangan').html('Keterangan Mutasi : ' + data.alasan_mutasi);


            let status = data.status_mutasi == "SELESAI"
                ? '<span class="badge bg-label-success mb-3">Selesai</span>'
                : (data.status_mutasi == "DITOLAK" ? '<span class="badge bg-label-danger mb-3">Ditolak</span>' : '<span class="badge bg-label-warning mb-3">Menunggu Persetujuan</span>')
            $('#statusmutasi').html(status);

            if (data.status_mutasi == 'PENDING') {
                $('#btnProses').css('display', 'block');
            } else {
                $('#btnProses').css('display', 'none');
            }

        })
        $('#modalDetail').modal('show');
        $.unblockUI();
    })

    $('#formAppMutasi').submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Proses Mutasi Karyawan",
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
                    url: "/approval-mutasi",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formAppMutasi").serialize(),
                    success: function (response) {
                        $("#modalDetail").modal("hide");
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
                            table.ajax.reload();
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
                    },
                });
            }
        });
    })
})