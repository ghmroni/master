_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $('#tabelPendidikan').DataTable();
        $.unblockUI();
    }, 200);

    // KELUARGA
    $(document).on('click', '.btnKeluarga', function () {
        var token = $(this).data('token');

        table1 = $("#tabelKeluarga").DataTable({
            destroy: true,
            ajax: "/ajax-keluarga/?token=" + encodeURIComponent(token),
            processing: true,
            serverSide: true,
            responsive: true,
            // aaSorting: [[0, "asc"]],
            columns: [
                {
                    data: "status_hubungan",
                },
                {
                    data: "nama_keluarga",
                },
                {
                    data: "kk_keluarga",
                },
                {
                    data: "ktp_keluarga",
                },
                {
                    data: "tmptlahir_keluarga",
                },
                {
                    data: "lahir",
                    name: "lahir",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "kelamin",
                    name: "kelamin",
                    orderable: true,
                    searchable: true,
                },

            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [0],
                    orderable: false
                },
                {
                    targets: [2, 3, 5, 6],
                    className: "text-center",
                },
            ],
        });

    })

    // PENDIDIKAN
    $(document).on('click', '.btnPendidikan', function () {
        var token = $(this).data('token');
        table2 = $("#tabelNonformal").DataTable({
            destroy: true,
            ajax: "/ajax-nonformal/?token=" + encodeURIComponent(token),
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [[2, "asc"]],
            columns: [
                {
                    data: "lembaga_nonformal",
                },
                {
                    data: "program_nonformal",
                },
                {
                    data: "awal",
                    name: "awal",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "akhir",
                    name: "akhir",
                    orderable: true,
                    searchable: true,
                },

            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [2, 3],
                    className: "text-center",
                },
            ],
        });
    })

    // PAKLARING
    $(document).on('click', '.btnPaklaring', function () {
        var token = $(this).data('token');
        table3 = $("#tabelPaklaring").DataTable({
            destroy: true,
            ajax: "/ajax-paklaring/?token=" + encodeURIComponent(token),
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [[2, "asc"]],
            columns: [
                {
                    data: "nama",
                },
                {
                    data: "jabatan",
                },
                {
                    data: "awal",
                    name: "awal",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "akhir",
                    name: "akhir",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "lampiran",
                    name: "lampiran",
                    orderable: true,
                    searchable: false,
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [2, 3, 4],
                    className: "text-center",
                },
            ],
        });
    })

    // SERTIFIKAT
    $(document).on('click', '.btnSertifikat', function () {
        var token = $(this).data('token');
        table4 = $("#tabelSertifikat").DataTable({
            destroy: true,
            ajax: "/ajax-sertifikat/?token=" + encodeURIComponent(token),
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [[3, "asc"]],
            columns: [
                {
                    data: "nama",
                },
                {
                    data: "penyelenggara",
                },
                {
                    data: "keterangan",
                },
                {
                    data: "terbit",
                    name: "terbit",
                    orderable: true,
                    searchable: true,
                },
                {
                    data: "lampiran",
                    name: "lampiran",
                    orderable: true,
                    searchable: false,
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "dt-head-center",
                },
                {
                    targets: [3, 4],
                    className: "text-center",
                },
            ],
        });
    })

    $(document).on('click', '.lihatLampiran', function () {
        var lampiran = $(this).data('lampiran');
        _loading();
        var url = '/lampiran/view/' + lampiran;
        // buka window baru dengan ukuran tertentu
        window.open(
            url,
            'PaklaringWindow', // nama window
            'width=800,height=600,scrollbars=yes,resizable=yes'
        );
        $.unblockUI();
    })

    $(document).on('click', '.btnBack', function () {
        _loading();
        window.history.back();
        $.unblockUI();
    })

})