_loading();
$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const latitude = $('#lat').val(),
        longitude = $('#lng').val(),
        status = $('#status').val();

    let statusClass = (status === "A") ? "" : "inactive";

    // Inisialisasi peta
    const dragMapVar = L.map("dragMap").setView([latitude, longitude], 5); // Bekasi

    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
    }).addTo(dragMapVar);

    // Icon Custom
    const customIcon = L.divIcon({
        // html: '<i class="fas fa-street-view fa-2x" style="color:#000; font-weight:bold; text-shadow: 0 0 3px #fff, 0 0 6px #fff;"></i>',
        html: `
            <div class="marker-pin ${statusClass}">
                <i class="fas fa-street-view"></i>
            </div>
        `,
        iconSize: [25, 40],
        popupAnchor: [0, -23],
        className: "my-custom-icon",
    });

    let currentMarker;
    let circleLayer;

    currentMarker = L.marker([latitude, longitude], {
        icon: customIcon,
    }).addTo(dragMapVar);

    setTimeout(function () {
        let idLokasi = $("#idlokasi").val();
        if (idLokasi && idLokasi !== "") {
            // mode edit → field bisa diisi ulang
            $(".disabled").prop("readonly", false);
        } else {
            // mode tambah → field readonly
            $(".disabled").prop("readonly", true);
        }

        dragMapVar.whenReady(() => {
            dragMapVar.flyTo([latitude, longitude], 18, { // angka semakin kecil, peta semakin kecil
                animate: true,
                duration: 3 // durasi dalam detik
            });

            // Munculkan radius ketika sudah selesai zoom peta
            dragMapVar.once('zoomend', function () {
                const radius = $('#radius').val();
                if (radius) {
                    if (circleLayer) {
                        dragMapVar.removeLayer(circleLayer);
                    }
                    circleLayer = L.circle(currentMarker.getLatLng(), {
                        radius: parseFloat(radius),
                        color: "red",
                        weight: 1,
                        fillColor: "#f03",
                        fillOpacity: 0.3
                    }).addTo(dragMapVar);
                }
            });

        });

        $.unblockUI();
    }, 300);

    $(document).on("click", "#generateUrl", function (e) {
        e.preventDefault();
        _loading();

        // Zoom Out dulu
        dragMapVar.flyTo([-6.24085, 106.99386], 10, {
            animate: true,
            duration: 1.5,
        });
        // Hapus Marker sebelumnya jika ada
        if (currentMarker) {
            dragMapVar.removeLayer(currentMarker);
        }

        if (circleLayer) {
            dragMapVar.removeLayer(circleLayer);
            circleLayer = null;
        }

        $("#lat, #lng").val("");
        var url = $("#url").val();

        $.ajax({
            url: "/generate-url-maps",
            type: "GET",
            dataType: "json", // PENTING: Memastikan respon dibaca sebagai objek JSON
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // PENTING: Agar $request->ajax() di Laravel bernilai true
            },
            data: {
                url: url,
            },
            success: function (response) {
                $("#lat").val(response.lat);
                $("#lng").val(response.lng);

                currentMarker = L.marker([response.lat, response.lng], {
                    icon: customIcon,
                }).addTo(dragMapVar);

                setTimeout(() => {
                    $(".disabled").prop("readonly", false);
                    dragMapVar.whenReady(() => {
                        dragMapVar.flyTo([response.lat, response.lng], 18, { // angka semakin kecil, peta semakin kecil
                            animate: true,
                            duration: 3 // durasi dalam detik
                        });

                        // Munculkan radius ketika sudah selesai zoom peta
                        dragMapVar.once('zoomend', function () {
                            const radius = $('#radius').val();
                            if (radius) {
                                if (circleLayer) {
                                    dragMapVar.removeLayer(circleLayer);
                                }
                                circleLayer = L.circle(currentMarker.getLatLng(), {
                                    radius: parseFloat(radius),
                                    color: "red",
                                    weight: 1,
                                    fillColor: "#f03",
                                    fillOpacity: 0.3
                                }).addTo(dragMapVar);
                            }
                        });
                    });

                    fv.revalidateField("lat");
                    fv.revalidateField("lng");
                    $.unblockUI();
                }, 300);
            },
            error: function (xhr) {
                $.unblockUI();
                Swal.fire({
                    title: "Error",
                    text: xhr.responseJSON.error,
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

    });

    $('#lat, #lng').on('input', function () {
        const lat = $('#lat').val().trim();
        const lng = $('#lng').val().trim();

        const regex = /^-?\d+(\.\d+)?$/;

        if (regex.test(lat) && regex.test(lng)) {
            // kedua input sudah ada value dan valid angka
            reloadMaps(parseFloat(lat), parseFloat(lng));
        }
    });

    function reloadMaps(lat, lng) {
        if (currentMarker) {
            // kalau marker sudah ada → pindahkan posisinya
            currentMarker.setLatLng([lat, lng]);
        } else {
            // kalau marker belum ada → buat baru
            currentMarker = L.marker([lat, lng], {
                icon: customIcon,
            }).addTo(dragMapVar);
        }

        setTimeout(() => {
            $(".disabled").prop("readonly", false);
            dragMapVar.whenReady(() => {
                dragMapVar.flyTo([lat, lng], 18, { // angka semakin kecil, peta semakin kecil
                    animate: true,
                    duration: 3 // durasi dalam detik
                });

                // Munculkan radius ketika sudah selesai zoom peta
                dragMapVar.once('zoomend', function () {
                    const radius = $('#radius').val();
                    if (radius) {
                        if (circleLayer) {
                            dragMapVar.removeLayer(circleLayer);
                        }
                        circleLayer = L.circle(currentMarker.getLatLng(), {
                            radius: parseFloat(radius),
                            color: "red",
                            weight: 1,
                            fillColor: "#f03",
                            fillOpacity: 0.3
                        }).addTo(dragMapVar);
                    }
                });
            });

            fv.revalidateField("lat");
            fv.revalidateField("lng");
            $.unblockUI();
        }, 300);
    }

    dragMapVar.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        document.getElementById('lat').value = lat.toFixed(6);
        document.getElementById('lng').value = lng.toFixed(6);


        // Hapus marker sebelumnya jika ada
        if (currentMarker) {
            dragMapVar.removeLayer(currentMarker);
        }

        // Tambahkan marker baru yang bisa di-drag
        currentMarker = L.marker([lat, lng], {
            icon: customIcon,
            draggable: true
        }).addTo(dragMapVar)
            .bindPopup(`Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`)
            .openPopup();

        // Kalau ada radius, langsung buat circle di posisi marker baru
        if (circleLayer) {
            dragMapVar.removeLayer(circleLayer);
            circleLayer = null;
        }

        const radius = $('#radius').val();
        if (radius) {
            circleLayer = L.circle(currentMarker.getLatLng(), {
                radius: parseFloat(radius),
                color: "red",
                weight: 1,
                fillColor: "#f03",
                fillOpacity: 0.3
            }).addTo(dragMapVar);
        }

        // Tangani event dragend
        currentMarker.on('dragend', function (event) {
            const position = event.target.getLatLng();
            document.getElementById('lat').value = position.lat.toFixed(6);
            document.getElementById('lng').value = position.lng.toFixed(6);

            // Update popup dengan posisi baru
            currentMarker.setPopupContent(`Lat: ${position.lat.toFixed(6)}, Lng: ${position.lng.toFixed(6)}`)
                .openPopup();

            if (circleLayer) {
                circleLayer.setLatLng(position);
            }
        });


    });

    $("#radius").on("input", function () {
        let lat = $('#lat').val();
        let lng = $('#lng').val();
        let radius = $(this).val();

        if (circleLayer) {
            dragMapVar.removeLayer(circleLayer);
            circleLayer = null;
        }

        if (radius) {
            circleLayer = L.circle([lat, lng], {
                radius: parseFloat(radius), // radius dalam meter
                color: "red",
                weight: 1,
                fillColor: "#f03",
                fillOpacity: 0.3, // transparansi kasih 0
            }).addTo(dragMapVar);
        }

    });

    var theme = $("html").hasClass("light-style") ? "default" : "default-dark",
        basicTree = $("#jstree-project");

    $(document).on("click", "#changeLokasi, #lokasipekerjaan", function () {
        _loading();
        basicTree.jstree({
            core: {
                data: {
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
        setTimeout(() => {
            $.unblockUI();
        }, 300);
    });

    $("#modalLokasi").on("shown.bs.modal", function () {
        var tree = $("#jstree-project").jstree(true);
        // tutup semua node
        tree.close_all();
        // hilangkan semua selected node
        tree.deselect_all(true);
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
            $("#projectid").val(selectedId);
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
                badges.push(
                    '<span class="badge ' +
                    color[idx % color.length] + // looping warna
                    ' me-1 text-black">' +
                    node.text +
                    "</span>",
                );
            });

            // tampilkan badge di form
            $("#lokasipekerjaan").html(badges.join(" "));

            setTimeout(() => {
                fv.revalidateField("projectid");
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
                    confirmButton: "btn btn-primary waves-effect waves-light",
                },
                buttonsStyling: false,
            });
        }
    });

    const formValidationlokasi = document.getElementById("formLokasi");
    const fv = FormValidation.formValidation(formValidationlokasi, {
        fields: {
            projectid: {
                validators: {
                    notEmpty: {
                        message: "Lokasi Penempatan masih kosong.",
                    },
                },
            },
            // url: {
            //     validators: {
            //         notEmpty: {
            //             message: "Inputan masih kosong.",
            //         },
            //         uri: {
            //             message: "Format URL tidak valid.",
            //             allowLocal: false, // biar hanya URL valid (http/https)
            //         },
            //     },
            // },
            lat: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                    regexp: {
                        regexp: /^-?\d+(\.\d+)?$/,
                        message: "Latitude harus berupa angka.",
                    },
                },
            },
            lng: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                    regexp: {
                        regexp: /^-?\d+(\.\d+)?$/,
                        message: "Longitude harus berupa angka.",
                    },
                },
            },
            nama: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                },
            },
            radius: {
                validators: {
                    notEmpty: {
                        message: "Inputan masih kosong.",
                    },
                    regexp: {
                        regexp: /^[0-9]+$/,
                        message: "Radius harus berupa angka.",
                    },
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

    formValidationlokasi.addEventListener("submit", function (e) {
        e.preventDefault();
        fv.validate().then(function (status) {
            if (status === "Valid") {
                Swal.fire({
                    title: "Konfirmasi!",
                    text: "Simpan Lokasi Absensi?",
                    icon: "question",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton:
                            "btn btn-primary me-3 waves-effect waves-light",
                        cancelButton: "btn btn-outline-secondary waves-effect",
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        _loading();
                        var formData = $("#formLokasi").serialize();

                        $.ajax({
                            url: "/lokasi-absensi",
                            type: "POST",
                            data: formData,
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
                                    window.location.href = "/lokasi-absensi";
                                });
                            },
                            error: function (error) {
                                $.unblockUI();
                                let errorMessage = error.statusText;
                                let errorTitle = `Error ${error.status}`;

                                // Coba parse JSON jika ada responseText
                                if (error.responseText) {
                                    try {
                                        let response = JSON.parse(
                                            error.responseText,
                                        );
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
            }
        });
    });
});
