_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Inisialisasi peta
    const dragMapVar = L.map("dragMap").setView([-6.24085, 106.99386], 18); // Bekasi
    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(dragMapVar);

    const markerList = []; // Menyimpan semua marker dengan koordinat

    const expandElementList = [].slice.call(
        document.querySelectorAll(".card-expand"),
    );
    if (expandElementList) {
        expandElementList.map(function (expandElement) {
            expandElement.addEventListener("click", (event) => {
                event.preventDefault();
                // Toggle class ti-arrows-maximize & ti-arrows-minimize
                Helpers._toggleClass(
                    expandElement.firstElementChild,
                    "ti-arrows-maximize",
                    "ti-arrows-minimize",
                );

                expandElement
                    .closest(".card")
                    .classList.toggle("card-fullscreen");

                if (window.dragMapVar) {
                    setTimeout(() => {
                        dragMapVar.invalidateSize();
                    }, 300); // kasih jeda biar transisi CSS selesai
                }
            });
        });
    }

    // Toggle fullscreen on esc key
    document.addEventListener("keyup", (event) => {
        event.preventDefault();
        //Esc button
        if (event.key === "Escape") {
            const cardFullscreen = document.querySelector(".card-fullscreen");
            // Toggle class ti-arrows-maximize & ti-arrows-minimize

            if (cardFullscreen) {
                Helpers._toggleClass(
                    cardFullscreen.querySelector(".card-expand").firstChild,
                    "ti-arrows-maximize",
                    "ti-arrows-minimize",
                );
                cardFullscreen.classList.toggle("card-fullscreen");

                if (window.dragMapVar) {
                    setTimeout(() => {
                        dragMapVar.invalidateSize();
                    }, 300); // kasih jeda biar transisi CSS selesai
                }
            }
        }
    });

    dragMapVar.on("zoomend", function () {
        dragMapVar.invalidateSize();
    });

    table = $("#tableMaps").DataTable({
        ajax: "",
        processing: true,
        serverSide: true,
        aaSorting: [[0, "asc"]],
        drawCallback: function () {
            var tooltipTriggerList = [].slice.call(
                document.querySelectorAll(
                    '#tableMaps [data-bs-toggle="tooltip"]',
                ),
            );
            var tooltipList = tooltipTriggerList.map(
                function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                },
            );
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
            },
            {
                data: "penempatan",
                name: "penempatan",
                orderable: true,
                searchable: true,
            },
            {
                data: "nama_koordinat",
            },
            {
                data: "radius",
                render: function (data, type, row) {
                    if (data) {
                        return data + " meter";
                    }
                    return "-"; // kalau kosong
                }
            },
            {
                data: "koordinat",
                name: "koordinat",
                orderable: false,
                searchable: false,
            },
            {
                data: "status",
                name: "status",
                orderable: true,
                searchable: true,
            },
            {
                data: "action",
                name: "action",
                orderable: true,
                searchable: true,
            },
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "dt-head-center align-top",
            },
            {
                targets: [0, 3, 5, 6],
                className: "text-center",
            },
        ],
    });

    $(document).on("click", ".switchStatus", function (e) {
        e.preventDefault();
        var status = $(this).data("status");
        var nama = $(this).data("nama");
        var token = $(this).data("token");
        var currentStatus = status === "A" ? "Aktifkan" : "Nonaktifkan";
        Swal.fire({
            title: "Konfirmasi!",
            html:
                currentStatus + " Mesin Web <br><strong>" + nama + "</strong>",
            icon: "question",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: "Ya, Rubah",
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
                    url: "/lokasi-absensi/status",
                    type: "POST",
                    data: { token: token },
                    success: function (response) {
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
                            table.ajax.reload(null, false);
                            reloadMaps()
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

    $(document).on("click", ".hapusLokasi", function (e) {
        e.preventDefault();
        var nama = $(this).data("nama");
        var token = $(this).data("token");

        Swal.fire({
            title: "Konfirmasi!",
            html: "Hapus Mesin Web <br><strong>" + nama + "</strong>",
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
                    url: "/destroy-lokasi-absensi",
                    type: "POST",
                    data: { token: token },
                    success: function (response) {
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
                            table.ajax.reload(null, false);
                            setTimeout(function () {
                                reloadMaps()
                            }, 300);
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

    setTimeout(function () {
        $.unblockUI();
        reloadMaps()
    }, 300);

    function reloadMaps() {
        $.getJSON("/maps-lokasi-absensi", function (response) {
            response.forEach(function (item) {
                if (item.tag_location) {
                    var latLng = item.tag_location.split(",");
                    const lat = parseFloat(latLng[0]);
                    const lng = parseFloat(latLng[1]);

                    // warna badge
                    var color = [
                        "text-success",
                        "text-warning",
                        "text-primary",
                        "text-info",
                        "text-secondary",
                        "text-dark",
                    ];

                    let status = item.status;
                    let statusClass = status === "A" ? "" : "inactive";

                    const customIcon = L.divIcon({
                        html: `
                            <div class="marker-pin ${statusClass}">
                                <i class="fas fa-street-view"></i>
                            </div>
                        `,
                        iconSize: [25, 40],
                        popupAnchor: [0, -23],
                        className: "my-custom-icon",
                    });

                    // bikin badge dari lokasi_absen
                    var badges = [];
                    if (item.lokasi_absen && item.lokasi_absen.length > 0) {
                        $.each(item.lokasi_absen, function (idx, lokasi) {
                            badges.push(
                                '<span class=" ' +
                                color[idx % color.length] + // looping warna
                                ' fw-bold">' +
                                lokasi +
                                "</span>",
                            );
                        });
                    }

                    const marker = L.marker([lat, lng], {
                        icon: customIcon,
                    }).addTo(dragMapVar).bindPopup(`
                        <div style="min-width:200px;" class="row">
                            <small class="text-muted" style="font-size:9px;">Project</small>
                            <div class="mb-1">${badges.join("<br>")}</div>
                            <small class="text-muted" style="font-size:9px;">Nama Koordinat</small>
                            <span class="fw-light fw-small fw-bold mb-1">${item.nama_koordinat} (${item.radius} m)</span>
                            <small class="text-muted" style="font-size:9px;">Action</small>
                            <div>
                                <button class="btn btn-primary btn-xs btn-block detailLokasi" data-token=${item.token}>Detail</button>
                            </div>
                        </div>
                    `);

                    // RADIUS
                    if (item.radius) {
                        L.circle([lat, lng], {
                            radius: parseFloat(item.radius), // radius dalam meter
                            color: "red",
                            weight: 1,
                            fillColor: "#f03",
                            fillOpacity: 0.3, // transparansi kasih 0
                        }).addTo(dragMapVar);
                    }

                    // Simpan ke markerList kalau mau dipakai lagi
                    markerList.push({
                        lat: lat,
                        lng: lng,
                        marker: marker,
                    });
                }
            });

            // Setelah semua marker ditambahkan, fokus ke bounds
            if (markerList.length > 0) {
                const group = L.featureGroup(markerList.map((m) => m.marker));
                const bounds = group.getBounds();

                // Ambil center dari semua marker
                const center = bounds.getCenter();

                // Tentukan zoom otomatis dari bounds
                const zoom = dragMapVar.getBoundsZoom(bounds);

                // Animasi terbang ke tengah semua marker

                // dragMapVar.flyTo(center, zoom, {
                //     animate: true,
                //     duration: 3,
                // });
                dragMapVar.flyToBounds(bounds, {
                    padding: [20, 20],
                    animate: true,
                    duration: 3,
                });
            } else {
                // fallback kalau tidak ada data
                dragMapVar.flyTo([-6.179183, 106.647034], 5, {
                    animate: true,
                    duration: 3,
                });
            }
        });
    }

    $(document).on("click", ".currentLocation", function () {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        var radius = $(this).data("radius");
        var coords = $(this).data("coordinat");
        var latLng = coords.split(",");

        const lat = parseFloat(latLng[0]).toFixed(5);
        const lng = parseFloat(latLng[1]).toFixed(5);

        const target = markerList.find(
            (m) =>
                parseFloat(m.lat).toFixed(5) === lat &&
                parseFloat(m.lng).toFixed(5) === lng,
        );

        if (radius) {
            L.circle([lat, lng], {
                radius: parseFloat(radius), // radius dalam meter
                color: "red",
                weight: 1,
                fillColor: "#f03",
                fillOpacity: 0.3, // transparansi kasih 0
            }).addTo(dragMapVar);
        }

        // Step 1: zoom out dulu
        dragMapVar.flyTo([lat, lng], 10, {
            animate: true,
            duration: 1.5,
        });

        // Step 2: setelah delay, zoom in ke lokasi
        setTimeout(() => {
            dragMapVar.flyTo([lat, lng], 18, {
                animate: true,
                duration: 2,
            });

            if (target) {
                target.marker.openPopup();
            }
        }, 1000);
    });

    $(document).on("click", ".detailLokasi", function () {
        _loading();
        var token = $(this).data("token");

        setTimeout(() => {
            window.location.href = "/lokasi-absensi/detail?token=" + token;
        }, 300);
    });


});
