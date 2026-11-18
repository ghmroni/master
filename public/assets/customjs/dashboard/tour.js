$(function () {
    const startBtn = document.querySelector('#mulaiTour');

    function setupTour(tour) {
        const backBtnClass = 'btn btn-sm btn-outline-secondary md-btn-flat waves-effect waves-light',
            nextBtnClass = 'btn btn-sm btn-primary btn-next waves-effect waves-light';
        tour.addStep({
            title: 'Title',
            text: 'Title atau Judul dari halaman',
            attachTo: { element: '.tourJudul', on: 'bottom' },
            buttons: [
                {
                    action: tour.cancel,
                    classes: backBtnClass,
                    text: 'Lewati'
                },
                {
                    text: 'Lanjut',
                    classes: nextBtnClass,
                    action: tour.next
                }
            ]
        });
        tour.addStep({
            title: 'Tampilan',
            text: 'Tampilan dengan mode gelap dan terang',
            attachTo: { element: '.tourTampilan', on: 'top' },
            buttons: [
                {
                    text: 'Lewati',
                    classes: backBtnClass,
                    action: tour.cancel
                },
                {
                    text: 'Kembali',
                    classes: backBtnClass,
                    action: tour.back
                },
                {
                    text: 'Lanjut',
                    classes: nextBtnClass,
                    action: tour.next
                }
            ]
        });
        tour.addStep({
            title: 'Notifikasi',
            text: 'Notifikasi informasi',
            attachTo: { element: '.tourNotifikasi', on: 'top' },
            buttons: [
                {
                    text: 'Lewati',
                    classes: backBtnClass,
                    action: tour.cancel
                },
                {
                    text: 'Kembali',
                    classes: backBtnClass,
                    action: tour.back
                },
                {
                    text: 'Lanjut',
                    classes: nextBtnClass,
                    action: tour.next
                }
            ]
        });
        tour.addStep({
            title: 'Profile',
            text: 'Profile pengguna',
            attachTo: { element: '.tourProfile', on: 'top' },
            buttons: [
                {
                    text: 'Lewati',
                    classes: backBtnClass,
                    action: tour.cancel
                },
                {
                    text: 'Kembali',
                    classes: backBtnClass,
                    action: tour.back
                },
                {
                    text: 'Lanjut',
                    classes: nextBtnClass,
                    action: tour.next
                }
            ]
        });
        tour.addStep({
            title: 'Halaman',
            text: 'Halaman yang sedang aktif',
            attachTo: { element: '.tourPage', on: 'top' },
            buttons: [
                {
                    text: 'Lewati',
                    classes: backBtnClass,
                    action: tour.cancel
                },
                {
                    text: 'Kembali',
                    classes: backBtnClass,
                    action: tour.back
                },
                {
                    text: 'Lanjut',
                    classes: nextBtnClass,
                    action: tour.next
                }
            ]
        });
        tour.addStep({
            title: 'Menu',
            text: 'Menu dan Submenu',
            attachTo: { element: '.tourMenu', on: 'right' },
            buttons: [
                {
                    text: 'Kembali',
                    classes: backBtnClass,
                    action: tour.back
                },
                {
                    text: 'Selesai',
                    classes: nextBtnClass,
                    action: tour.cancel
                }
            ]
        });

        return tour;
    }

    if (startBtn) {
        // On start tour button click
        startBtn.onclick = function () {
            const tourVar = new Shepherd.Tour({
                defaultStepOptions: {
                    scrollTo: false,
                    cancelIcon: {
                        enabled: false
                    }
                },
                useModalOverlay: true
            });

            setupTour(tourVar).start();
        };
    }


})