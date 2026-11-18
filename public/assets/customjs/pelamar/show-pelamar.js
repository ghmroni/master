_loading();

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


    setTimeout(function () {
        $.unblockUI();
    }, 200);

    table = $("#tabelPelamar").DataTable({
        ajax: "/pelamar",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        // responsive: true,
        columns: [
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: "nama",
            },
            {
                data: "noktp",
            },
            {
                data: "email",
            },
            {
                data: "jenjang_pendidikan",
            },
            {
                data: "usia",
                name: "usia",
                orderable: true,
                searchable: false,
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
                targets: [0, 2, 4, 5, 6],
                className: "dt-center",
            },

        ],
    });

    $(document).on('click', '.riwayat', function () {
        _loading;
        var token = $(this).data('token');
        var nama = $(this).data('nama');
        $('#listRiwayat').empty();
        $.getJSON('/pelamar/history/?token=' + encodeURIComponent(token), function (response) {
            $.each(response.lowongan_apply, function (Id, data) {
                // ======================================= RECORD PEMANGGILAN ============================================
                // siapkan list item dari recordpemanggilan
                var listItems = '';
                $.each(data.pemanggilan.record_pemanggilan, function (j, record) {
                    // mapping status ke warna timeline
                    var timelineClass = 'list-group-timeline-secondary';
                    switch (record.status) {
                        case 'PENDING':
                            timelineClass = 'list-group-timeline-dark';
                            break;
                        case 'DITERIMA':
                            timelineClass = 'list-group-timeline-success';
                            break;
                        case 'PROSES':
                            timelineClass = 'list-group-timeline-warning';
                            break;
                        case 'USER':
                            timelineClass = 'list-group-timeline-info';
                            break;
                        case 'HRD':
                            timelineClass = 'list-group-timeline-primary';
                            break;
                        case 'GAGAL':
                            timelineClass = 'list-group-timeline-danger';
                            break;
                    }

                    listItems += `
                        <li class="list-group-item ${timelineClass}">
                            ${record.keterangan} 
                            <small class="text-muted">(${record.created_at_formatted})</small>
                        </li>
                    `;
                });

                // ======================================= LIST RECORD ============================================
                // Status Pemanggilan
                var labelStatus = 'badge bg-secondary ms-2';
                switch (data.pemanggilan.status) {
                    case 'PENDING':
                        labelStatus = 'badge bg-dark ms-2';
                        break;
                    case 'DITERIMA':
                        labelStatus = 'badge bg-success ms-2';
                        break;
                    case 'PROSES':
                        labelStatus = 'badge bg-warning ms-2';
                        break;
                    case 'USER':
                        labelStatus = 'badge bg-info ms-2';
                        break;
                    case 'HRD':
                        labelStatus = 'badge bg-primary ms-2';
                        break;
                    case 'GAGAL':
                        labelStatus = 'badge bg-danger ms-2';
                        break;
                }

                $('#listRiwayat').append(`
                    <div class="card accordion-item mb-3 shadow">
                        <h2 class="accordion-header" id="heading`+ (Id + 1) + `">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                                data-bs-target="#`+ data.no_registrasi + `" aria-expanded="false"
                                aria-controls="`+ data.no_registrasi + `">
                                `+ data.created_at_formatted + ` <span class="${labelStatus} ms-2">` + data.pemanggilan.status + `</span>
                            </button >
                        </h2 >

                    <div id="`+ data.no_registrasi + `" class="accordion-collapse collapse"
                        data-bs-parent="#accordionRiwayat">
                        <div class="accordion-body mt-2">
                            <div class="text-center">
                                <h6 class="mb-1">`+ data.lowongan.judul + `</h6>
                                <small>`+ data.lowongan.pekerjaan_dilamar + `</small> |
                                <small class="text-info">`+ data.lowongan.penempatan + `</small>
                            </div>
                            <div class="demo-inline-spacing mt-3">
                                <ul class="list-group list-group-timeline">
                                    ${listItems}
                                </ul>
                            </div>
                        </div>
                    </div>
                    </div >
                    `);
            })
        })

        $('#modalHistory .modal-title').html('Riwayat ' + nama);
        $('#modalHistory').modal('show');
        $.unblockUI();
    })

    $(document).on('click', '.detailPelamar', function () {
        var token = $(this).data('token');
        window.location.href = '/pelamar/detail?token=' + encodeURIComponent(token);
    })
})