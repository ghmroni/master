_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const btnChange = $(".changeProject");
    var theme = $("html").hasClass("light-style")
        ? "default"
        : "default-dark",
        basicTree = $("#jstree-project");


    setTimeout(function () {
        $.unblockUI();
    }, 300);

    $('#tglefektif').flatpickr({
        monthSelectorType: "static",
        minDate: new Date().setMonth(new Date().getMonth() - 1),
        // static: true
    });

    table = $('#dataTable').DataTable({
        ajax: '',
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
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

    // var karyawanList = {};
    $(document).on('click', '.Tambah', function () {
        _loading();
        $('#boxDetail').css('display', 'none');
        $('#formMutasi')[0].reset();
        $('#myModal .modal-title').text('Mutasi Karyawan');
        $('#myModal').modal('show');
        $('#karyawanid').empty();
        $('#karyawanid').append('<option value="">pilih</option>');
        karyawanList = {};
        $.getJSON('/list-karyawan', function (response) {
            $.each(response, function (Id, data) {
                karyawanList[data.id] = data; // simpan detail di object JS
                $('#karyawanid').append('<option value="' + data.id + '" >' + data.nip + ' - ' + data.nama + '</option>');
            })
        })
        $.unblockUI();
    })

    $(document).on('change', '#karyawanid', function () {
        $('#boxDetail').css('display', 'none');
        _loading();
        var id = $(this).val();
        var detail = karyawanList[id];// ambil detail dari cache

        var lokasi = [];
        var color = ['bg-success', 'bg-warning', 'bg-primary', 'bg-info', 'bg-secondary', 'bg-dark'];
        $.each(detail.lokasi_penempatan, function (id, data) {
            lokasi.push('<span class="badge ' + color[id] + ' me-1 text-black">' + data + '</span>');
        })

        $('#boxDetail #vnip').html(detail.nip);
        $('#boxDetail #vnama').html(detail.nama);
        $('#boxDetail #projectold').val(detail.project_detail);
        $('#boxDetail #vlokasi').html(lokasi.join(" "));

        $('#boxDetail').css('display', 'block');

        $("#formMutasi #project").val(null);
        $("#formMutasi #dtproject").val(null);
        $("#formMutasi #catatan").val(null);
        $("#formMutasi #lokasipekerjaan").html('');
        fv.revalidateField("karyawanid");
        $.unblockUI();
    });

    // TAMPILKAN MODAL PERUBAHAN PROJECT

    $(document).on("click", "#changeLokasi, #lokasipekerjaan", function () {

        var dtproject = $('#projectold').val();
        var project = dtproject.split(',');

        _loading();

        // reset tree (destroy dulu biar tidak double inisialisasi)
        if (basicTree.jstree(true)) {
            basicTree.jstree("destroy").empty();
        }

        basicTree.jstree({
            core: {
                data: {
                    // url: "/mutasi/lokasitujuan/?project=" + project[0],
                    url: "/akses-project-tree",
                    data: function (node) {
                        return { id: node.id };
                    },
                    dataType: "json",
                },
                check_callback: true,
                themes: {
                    responsive: false,
                    name: theme,
                },
            },
            // plugins: ["state", "contextmenu", "wholerow"],
            plugins: ["search", "types", "state"],
            types: {
                default: { icon: "far fa-folder-open text-warning" },
            },
        });
        // hilangkan semua selected node
        basicTree.jstree("deselect_all", true);
        $("#modalLokasi").modal("show");
        $.unblockUI();
    });

    var to = false;
    $("#cari").keyup(function () {
        if (to) {
            clearTimeout(to);
        }
        to = setTimeout(function () {
            var v = $("#cari").val();
            basicTree.jstree(true).search(v);
        }, 250);
    });


    $("#modalLokasi").on("shown.bs.modal", function () {
        var tree = $("#jstree-project").jstree(true);
        // tutup semua node
        tree.close_all();
        // hilangkan semua selected node
        tree.deselect_all(true);
    });

    // PERUBAHAN PROJECT
    $(document).on("click", ".changeProject", function () {
        _loading();
        var tree = $("#jstree-project").jstree(true);
        var selectedId = tree.get_selected()[0];

        if (selectedId) {
            var selectedNode = tree.get_node(selectedId);
            // ambil semua parent id dari node ini
            var parentIds = selectedNode.parents.slice().reverse();
            // tambahkan id node yang dipilih
            parentIds.push(selectedNode.id);
            // filter supaya '#' tidak ikut
            var cleanIds = parentIds.filter(function (id) {
                return id !== "#";
            });
            // gabungkan jadi string dengan koma
            var idsString = cleanIds.join(",");
            // lempar ke input
            $("#project").val(selectedId);
            $("#dtproject").val(idsString);

            // buat badge untuk setiap node di path
            var color = [
                "bg-success",
                "bg-warning",
                "bg-primary",
                "bg-info",
                "bg-secondary",
                "bg-dark",
            ];
            var badges = [];
            $.each(cleanIds, function (idx, id) {
                var node = tree.get_node(id);
                badges.push('<span class="badge ' + color[idx % color.length] + ' me-1 text-black">' + node.text + "</span>");
            });

            // tampilkan badge di form
            $("#lokasipekerjaan").html(badges.join(" "));

            setTimeout(() => {
                fv.revalidateField("project");
                $("#modalLokasi").modal("hide");
                $.unblockUI();
            }, 300);
        } else {
            $.unblockUI();
            Swal.fire({
                title: "Oops!",
                text: "Silahkan pilih lokasi terlebih dahulu.",
                icon: "error",
                allowOutsideClick: false,
                showClass: {
                    popup: "animate__animated animate__shakeX",
                },
                hideClass: {
                    popup: `
                        animate__animated
                        animate__bounceOut
                        animate__faster
                    `,
                },
                customClass: {
                    confirmButton:
                        "btn btn-primary waves-effect waves-light",
                },
                buttonsStyling: false,
            });
        }
    });
    // END PERUBAHAN PROJECT

    const formValidationMutasi = document.getElementById("formMutasi");
    const fv = FormValidation.formValidation(formValidationMutasi, {
        fields: {
            karyawanid: {
                validators: {
                    notEmpty: {
                        message: "Silahkan pilih karyawan",
                    },
                },
            },
            project: {
                validators: {
                    notEmpty: {
                        message:
                            "Silahkan pilih ulang project tujuan",
                    },
                },
            },
            tglefektif: {
                validators: {
                    notEmpty: {
                        message: "Tanggal Efektif masih kosong",
                    },
                },
            },
            alasan: {
                validators: {
                    notEmpty: {
                        message: "Keterangan masih kosong",
                    },
                    callback: {
                        message: "Inputan tidak sesuai",
                        callback: function (input) {
                            // Pastikan tidak kosong dan tidak diawali spasi
                            return input.value.trim() !== "" && !/^\s/.test(input.value);
                        }
                    }
                },
            },
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleInvalidClass: "is-invalid",
                eleValidClass: "",
                rowSelector: ".row, .col-md-6, .col-md-12",
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
        },
    });

    formValidationMutasi.addEventListener('submit', function (e) {
        e.preventDefault();

        fv.validate().then(function (status) {
            if (status === 'Valid') {
                // submit form jika semua valid
                $.ajax({
                    url: "/mutasi-karyawan",
                    type: "POST",
                    data: $("#formMutasi").serialize(),
                    success: function (response) {
                        $('#myModal').modal('hide');
                        $.unblockUI();
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1500,
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
    });

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

        })
        $('#modalDetail').modal('show');
        $.unblockUI();
    })

    $(document).on('click', '.Batal', function () {
        var token = $(this).data('token');
        Swal.fire({
            // title: "Konfirmasi!",
            text: "Alasan Pembatalan Mutasi Karyawan.",
            icon: "info",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Batalkan!",
            cancelButtonText: "Tidak",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton:
                    "btn btn-outline-secondary waves-effect waves-light",
            },
            buttonsStyling: false,
            input: 'textarea',
            inputPlaceholder: 'Alasan pembatalan...',
            inputValidator: (value) => {
                if (!value) {
                    return 'Keterangan wajib diisi!';
                }
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                let keterangan = result.value; // ambil isi input
                $.ajax({
                    url: "/batalkan-mutasi",
                    type: "POST",
                    dataType: "JSON",
                    data: { token: token, alasan: keterangan },
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