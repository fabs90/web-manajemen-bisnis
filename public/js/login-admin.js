const form = document.querySelector(".auth-form");
const btnSubmit = document.getElementById("btn-submit");

form.addEventListener("submit", function () {
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = `
            <span class="spinner"></span>
            <span>Memproses...</span>
        `;
});

function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "🙈" : "👁️";
}
