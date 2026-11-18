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

    table = $("#dataTable").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],

        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "nip",
            },
            {
                data: "nama",
            },
            {
                data: "lokasi",
                name: "lokasi",
                orderable: true,
                searchable: true,
            },

            {
                data: "tmt",
                name: "tmt",
                orderable: true,
                searchable: false,
            },
            {
                data: "sisaSaldo",
                name: "sisaSaldo",
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
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 1, 4, 5, 6],
                className: "text-center",
            },
        ],
        // responsive: true,
    });

    $(document).on("click", ".Detail", function () {
        var token = $(this).data("token");
        _loading();
        $.getJSON(
            "detail-recordcuti?token=" + encodeURIComponent(token),
            function (response) {
                $("#myModal #dataRecord").html("");
                if ($.isEmptyObject(response)) {
                    $("#myModal #dataRecord").html(`
                    <div class="alert alert-danger d-flex align-items-center mt-3" role="alert">
                        <span class="alert-icon text-danger me-2">
                          <i class="ti ti-ban ti-xs"></i>
                        </span>
                        Oops! Data cuti tidak ditemukan.
                    </div>
                `);
                } else {
                    $.each(response, function (tahun, data) {
                        let saldoInfo = data.saldo
                            .map(function (s) {
                                return `
                            <li>Quota: ${s.quota}, Carry Over: ${s.carry_over}, Terpakai: ${s.terpakai}, Sisa: ${s.remaining}</li>
                        `;
                            })
                            .join("");

                        let cutiRows = data.cuti
                            .map(function (c) {
                                return `
                            <tr>
                                <td>${c.tanggal_dari}</td>
                                <td>${c.tanggal_sampai}</td>
                                <td>${c.jumlah_hari} hari</td>
                                <td>${c.status}</td>
                            </tr>
                        `;
                            })
                            .join("");

                        let accordionItem = `
                        <div class="accordion mt-3 border shadow rounded" id="accordionWithIcon">
                            <div class="card accordion-item">
                                <h2 class="accordion-header d-flex align-items-center">
                                    <button type="button" class="accordion-button text-uppercase collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#accordionWithIcon${tahun}"
                                        aria-expanded="true">
                                        <i class="fas fa-folder-open ti-xs me-2"></i>
                                        Data Cuti Tahun ${tahun}
                                    </button>
                                </h2>

                                <div id="accordionWithIcon${tahun}" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-striped table-bordered" style="width:100%;font-size:12px">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Dari</th>
                                                    <th>Sampai</th>
                                                    <th>Hari</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>${cutiRows}</tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    `;

                        $("#myModal #dataRecord").append(accordionItem);
                    });
                }

                $("#myModal").modal("show");
            },
        );
        setTimeout(() => {
            $.unblockUI();
        }, 300);
    });
});
