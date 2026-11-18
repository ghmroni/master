_loading();
$(function () {
    function animateCounter(selector, target) {
        $({ Counter: 0 }).animate(
            { Counter: target },
            {
                duration: 2000, // durasi animasi (ms)
                easing: "swing",
                step: function (now) {
                    // format angka ribuan sesuai locale Indonesia
                    $(selector).html(
                        new Intl.NumberFormat("id-ID").format(Math.ceil(now)),
                    );
                },
            },
        );
    }

    setTimeout(function () {
        $.getJSON("/karyawan-dashboard", function (res) {
            animateCounter("#totalAktif", res.aktif);
            animateCounter("#totalKeluar", res.keluar);
        });

        $.unblockUI();
    }, 300);
});
