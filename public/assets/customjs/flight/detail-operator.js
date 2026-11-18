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

    table = $("#tableDetail").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            // Inisialisasi ulang semua tooltip di dalam tabel setelah data muncul
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll(
                    '#tableDetail [data-bs-toggle="tooltip"]',
                ),
            );
            var tooltipList = tooltipTriggerList.map(
                function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                },
            );
        },
        // scrollX: true, // wajib untuk horizontal scroll
        // fixedColumns: {
        //     leftColumns: 3, // NO + 2 kolom tanggal
        // },
        // fixedHeader: true, // aktifkan freeze header
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "tgl_arrival",
                name: "tgl_arrival",
            },
            {
                data: "tgl_departure",
                name: "tgl_departure",
            },
            {
                data: "aircraft_type",
                name: "aircraft_type",
            },
            {
                data: "aircraft_categori",
                name: "aircraft_categori",
            },
            {
                data: "registrasi",
                name: "registrasi",
            },
            {
                data: "arrival_flight",
                name: "arrival_flight",
            },
            {
                data: "arrival_from",
                name: "arrival_from",
            },
            {
                data: "sta",
                name: "sta",
            },
            {
                data: "ata",
                name: "ata",
            },
            {
                data: "departure_flight",
                name: "departure_flight",
            },
            {
                data: "departure_to",
                name: "departure_to",
            },
            {
                data: "std",
                name: "std",
            },
            {
                data: "atd",
                name: "atd",
            },
            {
                data: "status",
                name: "status",
            },
            {
                data: "remarks",
                name: "remarks",
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
                className: "dt-head-center align-top",
            },
            {
                targets: "_all",
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".detailReg", function () {
        _loading();
        var token = $(this).data("token");
        $.getJSON("/detail-flight?token=" + encodeURIComponent(token), function (res) {
            $("#formRegistrasi")[0].reset();
            $("#idreg").val(token);
            $("#namareg").val(res.aircraft_registrasi);
            $.unblockUI();
        });
        $("#modalRegistrasi").modal("show");
    });

    $("#formRegistrasi").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Registrasi",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
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
                    url: "/regflight",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formRegistrasi").serialize(),
                    success: function (response) {
                        $("#modalRegistrasi").modal("hide");
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
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

    $(document).on("click", ".addAta", function () {
        _loading();
        var token = $(this).data("token");
        $("#dtRow").empty();
        $.getJSON("/detail-flight?token=" + encodeURIComponent(token), function (res) {
            $("#formWaktu")[0].reset();
            $("#formWaktu #idreg").val(token);
            $("#formWaktu #dtRow").append(
                `
                    <div class="col mb-3">
                        <label class="form-label text-muted">Tanggal Arrival</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Tanggal" readonly value="` + res.arrival_tgl + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Arrival Flight</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Flight" readonly value="` + res.arrival_flight + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Arrival From</label>
                        <input type="text" class="form-control text-uppercase" placeholder="From" readonly value="` + res.arrival_from + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Arrival STA</label>
                        <input type="text" class="form-control text-uppercase" placeholder="STA" readonly value="` + res.arrival_sta + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label">Actual Time Arrival</label>
                        <input type="text" class="form-control text-uppercase" id="ata" name="ata" placeholder="00:00" required/>
                    </div>
                `,
            );

            $("#ata").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#ata").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });
            $.unblockUI();
        },
        );
        $("#modalWaktu .modal-title").html("Actual Time Arrival");
        $("#modalWaktu").modal("show");
    });

    $(document).on("click", ".addAtd", function () {
        _loading();
        var token = $(this).data("token");
        $("#dtRow").empty();
        $.getJSON("/detail-flight?token=" + encodeURIComponent(token), function (res) {
            $("#formWaktu")[0].reset();
            $("#formWaktu #idreg").val(token);
            $("#formWaktu #dtRow").append(
                `
                    <div class="col mb-3">
                        <label class="form-label text-muted">Tanggal Departure</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Tanggal" readonly value="` + res.departure_tgl + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Departure Flight</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Flight" readonly value="` + res.departure_flight + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Departure To</label>
                        <input type="text" class="form-control text-uppercase" placeholder="To" readonly value="` + res.departure_to + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label text-muted">Departure STD</label>
                        <input type="text" class="form-control text-uppercase" placeholder="STD" readonly value="` + res.departure_std + `"/>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label">Actual Time Departure</label>
                        <input type="text" class="form-control text-uppercase" id="atd" name="atd" placeholder="00:00" required/>
                    </div>
                `,
            );

            $("#atd").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#atd").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });
            $.unblockUI();
        },
        );
        $("#modalWaktu .modal-title").html("Actual Time Departure");
        $("#modalWaktu").modal("show");
    });

    $("#formWaktu").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
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
                    url: "/time-flight",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formWaktu").serialize(),
                    success: function (response) {
                        $("#modalWaktu").modal("hide");
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
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

    $("#tglarrival").change(function () {
        $("#tgldeparture").val("");
    });

    var endDate = $("#tgldeparture").flatpickr({
        dateFormat: "Y-m-d",
    });

    var startDate = $("#tglarrival").flatpickr({
        dateFormat: "Y-m-d",
        // maxDate: "today",
        onChange: function (selectedDates) {
            var selectedDate = selectedDates[0]; // tanggal dari dipilih

            endDate.set("minDate", selectedDate);
        },
    });

    $(document).on("click", ".edit", function () {
        var token = $(this).data("token");
        _loading();
        $("#formEdit #dtLayanan").empty();
        $.getJSON("/detail-flight?token=" + encodeURIComponent(token), function (res) {
            $("#formEdit")[0].reset();
            $("#formEdit #idreg").val(token);
            // $("#formEdit #tglarrival").val(res.tgl_arrival);
            // $("#formEdit #tgldeparture").val(res.tgl_departure);
            startDate.setDate(res.tgl_arrival, true);
            endDate.setDate(res.tgl_departure, true);
            endDate.set("minDate", res.tgl_arrival);

            $("#formEdit #aircrafttype").val(res.aircraft_type);
            $("#formEdit #aircraftcategori").val(res.aircraft_categori);
            $("#formEdit #aircraftregistrasi").val(res.aircraft_registrasi);
            $("#formEdit #arrivalflight").val(res.arrival_flight);
            $("#formEdit #arrivalfrom").val(res.arrival_from);

            $("#formEdit #arrivalsta").val(res.arrival_sta);
            $("#formEdit #arrivalsta").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#formEdit #arrivalsta").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });

            $("#formEdit #arrivalata").val(res.arrival_ata);
            $("#formEdit #arrivalata").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#formEdit #arrivalata").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });

            $("#formEdit #departureflight").val(res.departure_flight);
            $("#formEdit #departureto").val(res.departure_to);

            $("#formEdit #departurestd").val(res.departure_std);
            $("#formEdit #departurestd").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#formEdit #departurestd").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });

            $("#formEdit #departureatd").val(res.departure_atd);
            $("#formEdit #departureatd").timepicker({
                show: "24:00",
                timeFormat: "H:i",
                orientation: isRtl ? "r" : "l",
            });
            // cegah huruf masuk
            $("#formEdit #departureatd").on("input", function () {
                this.value = this.value.replace(/[^0-9:]/g, "");
            });

            $("#formEdit #status").val(res.status);
            $("#formEdit #remarks").val(res.remarks);

            // Data Layanan
            let dtTitle = res.title;
            let dtCeklis = res.ceklis;
            if (dtTitle && dtTitle.trim() !== "") {
                let arr = dtTitle.split(",");
                let arrCeklis = dtCeklis.split(",");
                if (arr.length > 0) {
                    $("#formEdit #dtLayanan").append(`
                            <div class="divider divider-primary mb-0 mt-0">
                                <div class="divider-text text-uppercase fw-bold text-primary">Layanan Service</div>
                            </div>
                        `);
                    $.each(arr, function (i, val) {
                        $("#formEdit #dtLayanan").append(
                            `
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">` + val + `</label>
                                    <input type="text" name="ceklis[]" class="form-control text-uppercase" placeholder="X" value="` + arrCeklis[i] + `" maxlength="1" autocomplete="off">
                                </div>
                            `,
                        );
                    });
                }
            }

            $("#modalEdit .modal-title").html(
                res.cabang + " | " + res.sheet,
            );
            $("#modalEdit").modal("show");
            $.unblockUI();
        });
    });

    $(document).on("input", 'input[name="ceklis[]"]', function () {
        let val = $(this).val().toUpperCase();
        if (val !== "" && val !== "X") {
            // kalau bukan kosong dan bukan X, reset jadi kosong
            $(this).val("");
        }
    });

    $("#formEdit").submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Simpan Data Flight",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
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
                    url: "/store-flight",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formEdit").serialize(),
                    success: function (response) {
                        $("#modalEdit").modal("hide");
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
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

    $(document).on('click', '.hapus', function () {
        var token = $(this).data('token');
        $("#formDetail #dtRow").empty();
        $.getJSON("/detail-flight?token=" + encodeURIComponent(token), function (res) {

            $("#formDetail")[0].reset();
            $("#formDetail #idreg").val(token);
            $("#formDetail #dtRow").append(
                `
                    <div class="col mb-2">
                        <label class="form-label text-muted">Tanggal Arrival</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Tanggal" readonly value="` + res.arrival_tgl + `"/>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label text-muted">Tanggal Departure</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Tanggal" readonly value="` + res.departure_tgl + `"/>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label text-muted">Arrival Flight</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Flight" readonly value="` + res.arrival_flight + `"/>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label text-muted">Arrival From</label>
                        <input type="text" class="form-control text-uppercase" placeholder="From" readonly value="` + res.arrival_from + `"/>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label text-muted">Departure Flight</label>
                        <input type="text" class="form-control text-uppercase" placeholder="Flight" readonly value="` + res.departure_flight + `"/>
                    </div>
                    <div class="col mb-2">
                        <label class="form-label text-muted">Departure To</label>
                        <input type="text" class="form-control text-uppercase" placeholder="To" readonly value="` + res.departure_to + `"/>
                    </div>
                `,
            );
            $.unblockUI();
        });
        $("#detailFlight").modal("show");
    })

    $('#formDetail').submit(function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Konfirmasi!",
            text: "Hapus Data Flight",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "btn btn-danger me-3 waves-effect waves-light",
                cancelButton: "btn btn-outline-secondary waves-effect",
            },
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                _loading();
                $.ajax({
                    url: "/destroy-flight",
                    type: "POST",
                    dataType: "JSON",
                    data: $("#formDetail").serialize(),
                    success: function (response) {
                        $("#detailFlight").modal("hide");
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
                                if (response.errors) {
                                    var errors = $.parseJSON(
                                        error.responseText,
                                    );
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
});
