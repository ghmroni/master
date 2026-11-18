_loading();

$(function () {

    setTimeout(function () {
        $.unblockUI();
    }, 300);

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
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: 'lokasi',
                name: "lokasi",
                orderable: true,
                searchable: true,
            },
            {
                data: 'nip',
            },
            {
                data: 'nama',
            },
            {
                data: 'jabatan.name',
            },
            {
                data: 'kontrak_aktif.statuskontrak.name',
            },
            {
                data: "action",
                name: "action",
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
                targets: [0, 2, 5, 6],
                className: "text-center",
            },


        ],
        // responsive: true,
        // Mode Belum Cetak PKWT
        // createdRow: function (row, data, dataIndex) {

        //     if (!data.kontrak_aktif.pkwt_id) {
        //         $(row).addClass('bg-label-warning');
        //     }
        // }
    })

    let table1;

    $(document).on('click', '.Detail', function () {
        var token = $(this).data('token');
        var nama = $(this).data('nama');
        var nip = $(this).data('nip');
        _loading();

        if ($.fn.DataTable.isDataTable('#tableDetail')) {
            table1.destroy();
        }

        table1 = $("#tableDetail").DataTable({
            ajax: "/pkwt/detail/?token=" + token,
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [[0, "asc"]],
            columns: [
                {
                    data: "status",
                    name: "status",
                    orderable: false,
                    searchable: false,
                },
                {
                    data: "nomor_kontrak",
                },
                {
                    data: "tanggal",
                    name: "tanggal",
                    orderable: true,
                    searchable: false,
                },
                {
                    data: "kopsurat",
                    name: "kopsurat",
                    orderable: false,
                    searchable: false,
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
                    targets: [0, 1, 2, 4],
                    className: "text-center",
                }
            ],
        });

        $('#modalDetail .modal-title').html(nip + ' | ' + nama);
        $('#modalDetail').modal('show');
        $.unblockUI();

    })

    $(document).on('click', '.Cetak', function () {
        _loading();
        var token = $(this).data('token');
        var kopsurat = $('input[name="kopsurat_' + token + '"]:checked').val();
        var status = $('input[name="update_' + token + '"]:checked').val() || 'create';
        var url = '/pkwt/cetak/?token=' + encodeURIComponent(token) + '&status=' + status + '&kop=' + encodeURIComponent(kopsurat);

        if (status == 'create') {
            window.open(url, '_blank');
        } else {
            Swal.fire({
                title: 'Alasan Diperbaharui',
                input: 'textarea',
                inputPlaceholder: 'Alasan pembatalan...',
                inputAttributes: {
                    'aria-label': 'Tuliskan alasan Anda'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan wajib diisi!';
                    }
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonText: 'Proses',
                cancelButtonText: 'Close',
                customClass: {
                    confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                    cancelButton: 'btn btn-outline-secondary waves-effect waves-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(url + '&alasan=' + encodeURIComponent(result.value), '_blank');
                }
            });

        }
        $('#modalDetail').modal('hide');

        setTimeout(function () {

            table.ajax.reload(null, false);
            $.unblockUI();
        }, 1000);

    })
})