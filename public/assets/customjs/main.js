$(function () {
    $(document).on("click", ".menu-link", function (e) {
        const $link = $(this);

        // cek apakah link punya child submenu
        const hasChild =
            $link.closest(".menu-item").find(".menu-sub").length > 0;

        if (!hasChild) {
            // kalau tidak punya child, intercept klik
            e.preventDefault();
            _loading();

            const url = $link.attr("href");
            setTimeout(function () {
                window.location.href = url;
            }, 300);
        }
        // kalau punya child, biarkan default (expand submenu)
    });

    var select2 = $(".select2"),
        flatpickrDate = document.querySelector(".tanggal"),
        maxlengthInput = $(".maxlength");

    const numeralMask = document.querySelector(".number"),
        verticalScroll = document.getElementById("vertical-scroll"),
        prefixMask = document.querySelector(".hp"),
        phoneMask = document.querySelector(".phone"),
        faxMask = document.querySelector(".fax"),
        select2Icons = $(".select2-icons");

    //Vertical
    if (verticalScroll) {
        new PerfectScrollbar(verticalScroll, {
            wheelPropagation: false,
        });
    }

    // Date
    if (flatpickrDate) {
        flatpickrDate.flatpickr({
            monthSelectorType: "static",
            // static: true
        });
    }
    // Select2
    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: "pilih",
                dropdownParent: $this.parent(),
            });
        });
    }

    //Numeral
    if (numeralMask) {
        new Cleave(numeralMask, {
            numeral: true,
            numeralThousandsGroupStyle: "thousand",
        });
    }

    // Number Only
    document.querySelectorAll(".numberOnly").forEach(function (input) {
        new Cleave(input, {
            numeral: true,
            numeralDecimalScale: 0, // Tidak ada angka desimal
            numeralThousandsGroupStyle: "none",
        });
    });

    // HP
    if (prefixMask) {
        new Cleave(prefixMask, {
            prefix: "+62",
            blocks: [3, 3, 4, 4],
            // uppercase: true,
            numericOnly: true,
            // prefixMask: true
        });
    }
    // TLP
    if (phoneMask) {
        new Cleave(phoneMask, {
            // phone: true,
            // phoneRegionCode: 'ID'
            prefix: "0",
            blocks: [3, 4, 4], // contoh: 021-1234-5678
            delimiters: ["-", "-"],
            numericOnly: true,
        });
    }

    if (faxMask) {
        new Cleave(faxMask, {
            prefix: "0",
            blocks: [3, 4, 4], // contoh: 021-1234-5678
            delimiters: ["-", "-"],
            numericOnly: true,
        });
    }

    const timeout = 900000; // 900000 ms = 15 minutes
    var idleTimer = null;
    $("*").bind(
        "mousemove click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick",
        function () {
            clearTimeout(idleTimer);

            idleTimer = setTimeout(function () {
                document.getElementById("logout-form").submit();
            }, timeout);
        },
    );
    $("body").trigger("mousemove");

    $(".textarea_editor").wysihtml5({
        html: true,
        stylesheets: false,
    });
    $(".textarea_editor1").wysihtml5({
        html: true,
        stylesheets: false,
    });

    $(".textarea_editor2").wysihtml5({
        html: true,
        stylesheets: false,
    });

    // Bootstrap Max Length
    // --------------------------------------------------------------------
    if (maxlengthInput.length) {
        maxlengthInput.each(function () {
            $(this).maxlength({
                warningClass: "label label-success bg-success text-white",
                limitReachedClass: "label label-danger",
                separator: " dari ",
                preText: "Anda mengetik ",
                postText: " karakter tersedia.",
                validate: true,
                threshold: +this.getAttribute("maxlength"),
            });
        });
    }

    // Select2 Icons
    if (select2Icons.length) {
        // custom template to render icons
        function renderIcons(option) {
            if (!option.id) {
                return option.text;
            }
            var $icon =
                "<i class='" +
                $(option.element).data("icon") +
                " me-2'></i>" +
                option.text;

            return $icon;
        }
        select2Icons.wrap('<div class="position-relative"></div>').select2({
            dropdownParent: select2Icons.parent(),
            templateResult: renderIcons,
            templateSelection: renderIcons,
            escapeMarkup: function (es) {
                return es;
            },
        });
    }

    $(document).on("click", ".hapusNotifikasi", function () {
        var id = $(this).data("id");
        $.ajax({
            url: "/notif/markAsRead",
            type: "POST",
            dataType: "JSON",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                id: id,
            },
            success: function (response) {
                $(".loading-notifikasi").block({
                    message:
                        '<div class="spinner-border text-white" role="status"></div>',
                    timeout: 500,
                    css: {
                        backgroundColor: "transparent",
                        border: "0",
                    },
                    overlayCSS: {
                        opacity: 0.5,
                    },
                });

                let count = response.jumlah;

                // update badge di DOM
                let badge = document.querySelector(".badge-notifications");
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = "inline-block";
                    } else {
                        badge.style.display = "none";
                    }
                }

                // update footer
                let footer = document.querySelector(".markAllAsReadFooter");
                if (footer) {
                    if (count > 0) {
                        footer.innerHTML = `
                    <a href="javascript:void(0)"
                        class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center markAllAsRead">
                        <i class="ti ti-mail-opened fs-4 me-1"></i>Tandai semua dibaca
                    </a>`;
                    } else {
                        footer.innerHTML = `
                    <span class="d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                        Oops! Tidak ada notifikasi
                    </span>`;
                    }
                }
            },
        });
    });

    $(document).on("click", ".markAllAsRead", function () {
        $.getJSON("/notif/markReadAll", function (res) {
            $(".loading-notifikasi").block({
                message:
                    '<div class="spinner-border text-white" role="status"></div>',
                timeout: 500,
                css: {
                    backgroundColor: "transparent",
                    border: "0",
                },
                overlayCSS: {
                    opacity: 0.5,
                },
            });

            let count = res.jumlah;
            // update badge di DOM
            let badge = document.querySelector(".badge-notifications");
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            }
            // hapus semua notifikasi dari list
            $(".dropdown-notifications-item").remove();

            // update footer
            let footer = document.querySelector(".markAllAsReadFooter");
            if (footer) {
                if (count > 0) {
                    footer.innerHTML = `
                    <a href="javascript:void(0)"
                        class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center markAllAsRead">
                        <i class="ti ti-mail-opened fs-4 me-1"></i>Tandai semua dibaca
                    </a>`;
                } else {
                    footer.innerHTML = `
                    <span class="d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                        Oops! Tidak ada notifikasi
                    </span>`;
                }
            }
        });
    });
});

function showToast(title = "Info", body = "Pesan default", type = "success") {
    const toastAnimationExample = document.querySelector(".toast-ex");

    // reset dulu class icon
    const icon = toastAnimationExample.querySelector(".ti");
    icon.className = "ti ti-xs me-2"; // reset ke base class

    // tambahkan animasi dan posisi
    toastAnimationExample.classList.add(
        "animate__zoomIn",
        "top-0",
        "start-50",
        "translate-middle-x",
    );

    // ubah warna icon sesuai type
    if (type === "success") icon.classList.add("ti-check", "text-success");
    if (type === "error") icon.classList.add("ti-x", "text-danger");
    if (type === "warning")
        icon.classList.add("ti-alert-triangle", "text-warning");
    if (type === "info") icon.classList.add("ti-bell", "text-info");

    // ubah judul dan body
    toastAnimationExample.querySelector(".toast-title").textContent = title;
    toastAnimationExample.querySelector(".toast-body").textContent = body;

    // tampilkan toast
    const toastAnimation = new bootstrap.Toast(toastAnimationExample);
    toastAnimation.show();
}

function _loading() {
    $.blockUI({
        // message: `<p p class="d-flex justify-content-center" >
        //             <div class="sk-chase mx-auto">
        //                 <div class="sk-chase-dot"></div>
        //                 <div class="sk-chase-dot"></div>
        //                 <div class="sk-chase-dot"></div>
        //                 <div class="sk-chase-dot"></div>
        //                 <div class="sk-chase-dot"></div>
        //                 <div class="sk-chase-dot"></div>
        //             </div>
        //             <p class="mb-0 text-dark">Loading... </p>
        //           </p> `,
        message: `
            <div class="d-flex flex-column align-items-center justify-content-center">
            <!-- Kontainer utama sedikit dinaikkan ke 45px -->
                <div class="position-relative d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; margin: 0 auto 10px auto;">
        
                    <!-- 1. Loader SpinKit dengan Padding agar titik berputar di tepi luar -->
                    <div class="sk-chase" style="width: 100%; height: 100%; position: absolute; padding: 2px; box-sizing: border-box;"> 
                        <div class="sk-chase-dot"></div> 
                        <div class="sk-chase-dot"></div> 
                        <div class="sk-chase-dot"></div> 
                        <div class="sk-chase-dot"></div> 
                        <div class="sk-chase-dot"></div> 
                        <div class="sk-chase-dot"></div> 
                    </div>

                    <!-- 2. Custom Logo (Statis di Tengah, Tidak Ikut Berputar) -->
                    <img src="/assets/img/favicon/logohr.png" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; z-index: 10; position: relative;">
        
                </div>
            </div>
        `,
        // timeout: 1000,
        css: {
            backgroundColor: "transparent",
            color: "#fff",
            border: "0",
        },
        overlayCSS: {
            backgroundColor: $("html").hasClass("dark-style") ? "#000" : "#000",
            opacity: 0.7,
        },
    });
}
