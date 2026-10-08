function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "🙈" : "👁️";
}

// Animasi loading saat submit
const form = document.querySelector(".auth-form");
const btnSubmit = document.getElementById("btn-submit");

form.addEventListener("submit", function () {
    btnSubmit.disabled = true;
    btnSubmit.classList.add("btn-loading");
    btnSubmit.innerHTML = `
            <div class="spinner"></div>
            Memproses...
        `;
});

function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "🙈" : "👁️";
}
