// Generate color RGB
function randColorRGB() {
    return (
        "rgba(" +
        Math.floor(Math.random() * 255) +
        "," +
        Math.floor(Math.random() * 255) +
        "," +
        Math.floor(Math.random() * 255) +
        ", 1)"
    );
}

// Generate color HEX
function randColorHex() {
    return "#" + Math.floor(Math.random() * 16777215).toString(16);
}

// Read cookie by name
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == " ") c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Get XSRF token
function get_xsrf() {
    var xsrfToken = decodeURIComponent(readCookie("XSRF-TOKEN"));
    if (xsrfToken) {
        return xsrfToken;
    } else {
        return $('meta[name="csrf-token"]').attr("content");
    }
}

// ganti data profil
$(function () {
    $.ajax({
        type: "get",
        url: "../../api/v1/identitas",
        success: function (response) {
            var data = response.data.attributes;
            $('.brand-link').children('img').attr('alt', data.nama_aplikasi);
            $('.brand-link').children('span').text(data.nama_aplikasi);
            console.log(response)
        }
    });
});
