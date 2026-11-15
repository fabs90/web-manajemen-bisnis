function showTurnstileError(message) {
    const $error = $("#turnstile-error");
    if ($error.length) {
        $error.text(message).show();
    }
}

function hideTurnstileError() {
    const $error = $("#turnstile-error");
    if ($error.length) {
        $error.text("").hide();
    }
}

function onTurnstileSuccess() {
    hideTurnstileError();
}

function onTurnstileError() {
    showTurnstileError(
        "Verifikasi Cloudflare Turnstile gagal. Silakan muat ulang halaman dan coba lagi."
    );
}

function onTurnstileExpired() {
    showTurnstileError(
        "Verifikasi Cloudflare Turnstile kedaluwarsa. Silakan selesaikan verifikasi ulang."
    );
}

$(document).ready(function () {
    const $form = $(".auth-form");
    const $btnSubmit = $("#btn-submit");

    $form.on("submit", function () {
        $btnSubmit.prop("disabled", true).html(`
            <span class="spinner"></span>
            <span>Memproses...</span>
        `);
    });
});

function togglePassword(inputId, icon) {
    const $input = $("#" + inputId);
    const isPassword = $input.attr("type") === "password";
    $input.attr("type", isPassword ? "text" : "password");
    $(icon).text(isPassword ? "🙈" : "👁️");
}
