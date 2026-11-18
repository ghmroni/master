$(function () {
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"), },
    });

    _loading();

    document.getElementById('email').addEventListener('input', function (e) {
        e.target.value = e.target.value.toLowerCase();
    });

    document.getElementById('name').addEventListener('input', function (e) {
        e.target.value = e.target.value.toUpperCase();
    });

    setTimeout(function () {
        $('#formContact')[0].reset();
        $.unblockUI();
    }, 300);


    // Inisialisasi peta
    const dragMapVar = L.map('dragMap').setView([-6.24085, 106.99386], 12); // Bekasi

    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(dragMapVar);

    // Variabel untuk menyimpan marker aktif
    let currentMarker = null;

    // Tangani klik di peta
    dragMapVar.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        document.getElementById('latInput').value = lat.toFixed(6);
        document.getElementById('lngInput').value = lng.toFixed(6);


        // Hapus marker sebelumnya jika ada
        if (currentMarker) {
            dragMapVar.removeLayer(currentMarker);
        }

        // Icon Custom
        const customIcon = L.icon({
            iconUrl: '/assets/img/illustrations/map-marker1.png',
            iconSize: [38, 52],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        // Tambahkan marker baru yang bisa di-drag
        currentMarker = L.marker([lat, lng], {
            icon: customIcon,
            draggable: true
        }).addTo(dragMapVar)
            .bindPopup(`Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`)
            .openPopup();

        // Tangani event dragend
        currentMarker.on('dragend', function (event) {
            const position = event.target.getLatLng();
            document.getElementById('latInput').value = position.lat.toFixed(6);
            document.getElementById('lngInput').value = position.lng.toFixed(6);

            // Update popup dengan posisi baru
            currentMarker.setPopupContent(`Lat: ${position.lat.toFixed(5)}, Lng: ${position.lng.toFixed(5)}`)
                .openPopup();
        });

    });

    const bsValidationForms = document.querySelectorAll('.needs-validation');
    // Loop over them and prevent submission
    Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
        form.addEventListener(
            'submit',
            function (event) {
                // Map
                const locationInput = document.getElementById('latInput');
                const mapError = document.getElementById('map-error');


                if (!form.checkValidity() || !locationInput.value) {
                    event.preventDefault();
                    event.stopPropagation();
                    // Validasi Map
                    if (!locationInput.value) {
                        mapError.style.display = 'block';
                    }


                } else {
                    // Submit your form
                    event.preventDefault();
                    Swal.fire({
                        title: "Konfirmasi!",
                        text: "Simpan Kontak Perusahaan",
                        icon: "warning",
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
                                url: "/kontak",
                                type: "POST",
                                dataType: "JSON",
                                data: $('#formContact').serialize(),
                                success: function (response) {
                                    $.unblockUI();
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: response.message,
                                        icon: "success",
                                        showConfirmButton: false,
                                        timer: 1000,
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
                                        window.location.href = '/kontak'
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
                                                "btn btn-primary waves-effect waves-light",
                                        },
                                        buttonsStyling: false,
                                    });
                                },
                            });
                        }
                    });
                }

                form.classList.add('was-validated');
            },
            false
        );
    });


})