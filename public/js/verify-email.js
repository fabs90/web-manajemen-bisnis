const inputs = document.querySelectorAll(".otp-input");
inputs.forEach((input, index) => {
    input.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !input.value && index > 0) {
            inputs[index - 1].focus();
        }
    });
});

const formVerify = document.querySelector(
    'form[action*="account-verification.store"]',
);
const btnVerify = document.querySelector(".btn-primary");

formVerify.addEventListener("submit", function () {
    btnVerify.classList.add("loading");
    btnVerify.disabled = true;
    btnVerify.innerHTML = `<span class="spinner"></span>Memverifikasi...`;
});

document.addEventListener("paste", function (e) {
    let activeElement = document.activeElement;
    let otpInputs = document.querySelectorAll(".otp-input");
    let isOtpField = Array.from(otpInputs).includes(activeElement);

    if (!isOtpField && !otpInputs[0].closest("form").contains(activeElement))
        return;

    e.preventDefault(); // Mencegah paste default

    let pastedData = (e.clipboardData || window.clipboardData).getData("text");
    let otpDigits = pastedData.replace(/\D/g, "").slice(0, 6); // Ambil hanya angka, max 6
    if (otpDigits.length < 1) return;
    otpInputs.forEach((input, index) => {
        if (otpDigits[index]) {
            input.value = otpDigits[index];
        } else {
            input.value = ""; // Kosongkan yang tersisa
        }
    });

    let lastFilledIndex = otpDigits.length - 1;
    if (lastFilledIndex < 5) {
        otpInputs[lastFilledIndex].focus();
    } else {
        // Kalau sudah 6 digit, langsung fokus ke tombol Verifikasi
        document.querySelector(".btn-primary").focus();
    }
});

document.querySelectorAll(".otp-input").forEach((input) => {
    input.addEventListener("keyup", function (e) {
        if (e.key === "Enter") {
            let allFilled = Array.from(
                document.querySelectorAll(".otp-input"),
            ).every((inp) => inp.value.length === 1);
            if (allFilled) {
                document.querySelector(".btn-primary").click();
            }
        }
    });
});
