$(function () {
    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"), },
    });

    _loading();

    // Inisialisasi peta
    const dragMapVar = L.map('dragMap').setView([-6.24085, 106.99386], 18); // Bekasi

    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(dragMapVar);

    // Icon Custom
    const customIcon = L.icon({
        iconUrl: '/assets/img/illustrations/map-marker1.png',
        iconSize: [25, 40],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    const markerList = []; // Menyimpan semua marker dengan koordinat

    $('#keterangan').html('<i class="fa fa-spin fa-spinner"></i> mencari lokasi');
    $('#ket').html('<i class="fa fa-spin fa-spinner"></i> mencari lokasi');
    $('#daftarLokasi').html('<center><i class="fa fa-spin fa-spinner fa-3x text-primary"></i></center>');

    setTimeout(function () {

        $.getJSON('/kontak', function (res) {
            $('#keterangan').html(res.length + ' lokasi ditemukan');
            $('#ket').html(res.length + ' lokasi tersedia');
            $('#daftarLokasi').empty();
            $.each(res, function (id, item) {

                //Daftar Lokasi
                const maxLength = 45;
                const alamat = item.alamat.length > maxLength
                    ? item.alamat.substring(0, maxLength) + '...'
                    : item.alamat;

                $('#daftarLokasi').append(`
                    <div class="lihatLokasi mb-3" style="cursor:pointer" data-lat="${item.latitude}" data-lng="${item.longitude}">
                        <div class="card shadow border border-warning">
                            <img class="card-img-top mb-0" src="/assets/img/illustrations/slide5.jpg" alt="gambar" height="100px" />
                            <div style="padding:10px">
                                <span class="card-title text-dark">`+ item.name + `</span>
                                <p class="card-text"><small class="text-muted">`+ alamat + `</small></p>
                                <a href="javascript:void(0);" class="btn btn-warning btn-sm detail mb-0" data-slug=${item.slug}>Detail</a>
                            </div>
                        </div>
                    </div>
                `);

                // Maps / Peta
                const marker = L.marker([item.latitude, item.longitude], {
                    // L.marker([item.latitude, item.longitude], {
                    icon: customIcon,
                })
                    .addTo(dragMapVar)
                    // .bindPopup(`<b>${item.name}</b><br>${item.alamat}`);
                    .bindPopup(`
                        <div style="min-width:200px;" class="row">
                            <img src="/assets/img/illustrations/slide5.jpg" alt="image" style="width: 100%;border-radius:4px" class="mb-2">
                            <small class="text-muted" style="font-size:9px;">${item.kategori}</small>
                            <span class="fw-light fw-small fw-bold" >${item.name}</span>
                            <p style="margin:4px 0; font-size:13px;">${item.alamat}</p>
                            <div class="d-grid gap-2 col-lg-12 mx-auto mt-1">
                                <button class="btn btn-info btn-sm btn-block detail" data-slug=${item.slug}>Detail</button>
                            </div>
                        </div>
                    `);

                // Simpan referensi marker
                markerList.push({
                    lat: item.latitude,
                    lng: item.longitude,
                    marker: marker
                });
            });

        })

        // ZoomIn or ZoomOut tergantung dari nomor
        dragMapVar.whenReady(() => {
            dragMapVar.flyTo([-6.179183, 106.647034], 5, { // angka semakin kecil, peta semakin kecil
                animate: true,
                duration: 3 // durasi dalam detik
            });
        });

        $.unblockUI();
    }, 300);

    $(document).on('click', '.add-new', function () {
        _loading();
        window.location.href = '/tambah-kontak';
        $.unblockUI();
    });

    $(document).on('click', '.detail', function () {
        _loading();
        var slug = $(this).data('slug');
        window.location.href = '/kontak-detail/' + slug;
        $.unblockUI();
    });

    $(document).on('click', '.lihatLokasi', function () {
        const lat = parseFloat($(this).data('lat')).toFixed(5);
        const lng = parseFloat($(this).data('lng')).toFixed(5);

        const target = markerList.find(m =>
            parseFloat(m.lat).toFixed(5) === lat &&
            parseFloat(m.lng).toFixed(5) === lng
        );

        if (target) {
            dragMapVar.flyTo([lat, lng], 14); // Fokus ke marker
            target.marker.openPopup();          // Tampilkan popup
        }
    });



})