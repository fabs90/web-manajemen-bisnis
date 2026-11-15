let isTurnstileVerified = false;

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

function areRequiredFieldsFilled() {
    let allFilled = true;

    $(".auth-form")
        .find("input[required], select[required], textarea[required]")
        .each(function () {
            const value = $(this).val();
            if (value === null || $.trim(String(value)) === "") {
                allFilled = false;
                return false;
            }
        });

    return allFilled;
}

function updateSubmitButtonState() {
    const $btnSubmit = $("#btn-submit");
    if (!$btnSubmit.length) {
        return;
    }

    $btnSubmit.prop("disabled", !(areRequiredFieldsFilled() && isTurnstileVerified));
}

function onTurnstileSuccess() {
    isTurnstileVerified = true;
    hideTurnstileError();
    updateSubmitButtonState();
}

function onTurnstileError() {
    isTurnstileVerified = false;
    updateSubmitButtonState();
    showTurnstileError(
        "Verifikasi Cloudflare Turnstile gagal. Silakan muat ulang halaman dan coba lagi."
    );
}

function onTurnstileExpired() {
    isTurnstileVerified = false;
    updateSubmitButtonState();
    showTurnstileError(
        "Verifikasi Cloudflare Turnstile kedaluwarsa. Silakan selesaikan verifikasi ulang."
    );
}

$(document).ready(function () {
    const $form = $(".auth-form");
    const $btnSubmit = $("#btn-submit");

    updateSubmitButtonState();

    $form.on("input change", "input, select, textarea", function () {
        updateSubmitButtonState();
    });

    $form.on("submit", function (event) {
        if (!areRequiredFieldsFilled() || !isTurnstileVerified) {
            event.preventDefault();
            updateSubmitButtonState();

            if (!isTurnstileVerified) {
                showTurnstileError(
                    "Silakan selesaikan verifikasi Cloudflare Turnstile terlebih dahulu."
                );
            }
            return;
        }

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
