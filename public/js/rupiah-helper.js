// public/js/rupiah-helper.js
class RupiahHelper {
    static format(number, withPrefix = false) {
        if (number === "" || number == null) return "";
        let num = number.toString().replace(/\D/g, "");
        if (num === "0") return "0";
        let result = parseInt(num).toLocaleString("id-ID");
        return withPrefix ? "Rp " + result : result;
    }

    static unformat(str) {
        if (!str) return 0;
        return parseInt(str.toString().replace(/\D/g, "")) || 0;
    }

    static initAll(selector = ".rupiah") {
        document.querySelectorAll(selector).forEach((input) => {
            // Format value awal (edit form)
            if (input.value.trim() !== "") {
                input.value = this.format(this.unformat(input.value));
            }

            // Gunakan input + keydown agar real-time & kursor tetap aman
            let isDeleting = false;

            input.addEventListener("keydown", (e) => {
                // Deteksi tombol Delete atau Backspace
                isDeleting = [8, 46].includes(event.keyCode);
            });

            input.addEventListener("input", (e) => {
                let cursorPos = e.target.selectionStart;
                let oldValue = e.target.value;
                let oldLength = oldValue.length;

                // Ambil angka saja
                let raw = this.unformat(oldValue);
                if (raw === 0 && oldValue !== "0") {
                    e.target.value = "";
                    return;
                }

                // Format ulang
                let formatted = this.format(raw);

                // Hitung perubahan panjang untuk koreksi kursor
                let diff = formatted.length - oldLength;

                e.target.value = formatted;

                // Koreksi posisi kursor (ini yang bikin mulus!)
                let newCursorPos = cursorPos + diff;
                // Kalau user lagi hapus dari kanan, jangan tambah diff
                if (isDeleting && oldValue[cursorPos - 1] === ".") {
                    newCursorPos = cursorPos;
                }

                // Set kursor ke posisi benar
                requestAnimationFrame(() => {
                    e.target.setSelectionRange(newCursorPos, newCursorPos);
                });
            });

            // Optional: blur = pastikan rapi
            input.addEventListener("blur", () => {
                if (input.value === "" || input.value === "0") {
                    input.value = "0";
                } else {
                    input.value = this.format(this.unformat(input.value));
                }
            });

            // Focus: biar gampang edit (opsional)
            input.addEventListener("focus", () => {
                if (input.value === "0") input.value = "";
            });
        });
    }

    static calculateTotal(inputs = ".rupiah", total = "#total_rupiah") {
        let sum = 0;
        document.querySelectorAll(inputs).forEach((el) => {
            sum += this.unformat(el.value);
        });
        const totalEl = document.querySelector(total);
        if (totalEl) {
            totalEl.value = this.format(sum);
        }
        return sum;
    }
}

window.RupiahHelper = RupiahHelper;
