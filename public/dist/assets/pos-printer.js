class PosPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.service = null;
        this.characteristic = null;

        // These are standard generic UUIDs for Chinese Thermal Printers.
        // Some use 'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
        // Default value for serviceUuid is the standard UUID, but we also include the alternative in optionalServices
        this.serviceUuid = "000018f0-0000-1000-8000-00805f9b34fb";
        this.characteristicUuid = "00002af1-0000-1000-8000-00805f9b34fb";
    }

    async connect() {
        if (!navigator.bluetooth) {
            alert(
                "Browser ini tidak mendukung Web Bluetooth API. Silakan gunakan Google Chrome di PC/Android.",
            );
            return false;
        }

        try {
            console.log("Meminta akses Bluetooth...");

            // We only request device once per session if not already requested
            if (!this.device) {
                // Check if browser supports getDevices() to remember previously connected printers
                let autoConnected = false;
                if (navigator.bluetooth.getDevices) {
                    const permittedDevices =
                        await navigator.bluetooth.getDevices();
                    if (permittedDevices.length > 0) {
                        // Use the last permitted device
                        this.device = permittedDevices[0];
                        autoConnected = true;
                        console.log(
                            "Menggunakan printer yang sudah diizinkan sebelumnya:",
                            this.device.name,
                        );
                    }
                }

                if (!autoConnected) {
                    this.device = await navigator.bluetooth.requestDevice({
                        // acceptAllDevices: true, // buka seluruh akses device
                        filters: [{ name: "RPP02N" }], // hardcode nama printer nya
                        optionalServices: [
                            this.serviceUuid, // '000018f0-0000-1000-8000-00805f9b34fb'
                            "e7810a71-73ae-499d-8c15-faa9aef0c3f2",
                            "49535343-fe7d-4ae5-8fa9-9fafd205e455",
                            "0000ff00-0000-1000-8000-00805f9b34fb", // HS6632M common service
                            "00001814-0000-1000-8000-00805f9b34fb",
                            "0000af30-0000-1000-8000-00805f9b34fb",
                        ],
                    });
                }

                this.device.addEventListener("gattserverdisconnected", () => {
                    console.log("Printer terputus");
                    this.server = null;
                    this.service = null;
                    this.characteristic = null;
                });
            }

            console.log("Menghubungkan ke GATT Server...");
            this.server = await this.device.gatt.connect();

            // Beri jeda sedikit agar perangkat siap setelah terhubung (menghindari error GATT Server disconnected)
            await new Promise((resolve) => setTimeout(resolve, 1000));

            console.log("Mendapatkan daftar layanan (services)...");
            let services = [];
            try {
                if (!this.device.gatt.connected) {
                    this.server = await this.device.gatt.connect();
                    await new Promise((resolve) => setTimeout(resolve, 1000));
                }
                services = await this.server.getPrimaryServices();
            } catch (error) {
                console.error("Gagal mendapatkan services:", error);
                throw new Error("Gagal membaca layanan dari printer.");
            }

            this.service = null;
            this.characteristic = null;

            // Cari service yang memiliki characteristic dengan akses 'write'
            for (let svc of services) {
                console.log("Memeriksa service:", svc.uuid);
                try {
                    const characteristics = await svc.getCharacteristics();
                    for (let char of characteristics) {
                        console.log(
                            "  - Characteristic:",
                            char.uuid,
                            " properties:",
                            "write:",
                            char.properties.write,
                            "writeWithoutResponse:",
                            char.properties.writeWithoutResponse,
                        );

                        if (
                            char.properties.write ||
                            char.properties.writeWithoutResponse
                        ) {
                            this.service = svc;
                            this.characteristic = char;
                            console.log("Kandidat cocok ditemukan!");
                            break;
                        }
                    }
                } catch (e) {
                    console.log(
                        "Gagal membaca characteristics untuk service",
                        svc.uuid,
                    );
                }
                if (this.characteristic) break; // Berhenti jika sudah ketemu
            }

            if (!this.service || !this.characteristic) {
                throw new Error(
                    "Tidak dapat menemukan layanan Bluetooth yang cocok di printer ini.",
                );
            }

            console.log("Printer berhasil terhubung!");
            return true;
        } catch (error) {
            console.error("Koneksi Bluetooth gagal:", error);
            // Reset state if connection failed so we can prompt again next time
            if (this.device && !this.device.gatt.connected) {
                this.device = null;
            }
            alert("Gagal terhubung ke printer: " + error.message);
            return false;
        }
    }

    async printReceipt(receiptData) {
        if (
            !this.device ||
            !this.device.gatt.connected ||
            !this.characteristic
        ) {
            const connected = await this.connect();
            if (!connected) return;
        }

        try {
            let bytes = this.buildReceiptBytes(receiptData);
            await this.sendBytesInChunks(bytes);
            console.log("Pencetakan selesai!");
        } catch (error) {
            console.error("Gagal mencetak:", error);
            alert("Gagal mencetak: " + error.message);
        }
    }

    // Receipt Template
    buildReceiptBytes(data) {
        let encoder = new TextEncoder();
        let bytes = [];

        // ESC/POS Commands
        const ESC = 0x1b;
        const GS = 0x1d;
        const INIT = [ESC, 0x40]; // Initialize printer
        const ALIGN_CENTER = [ESC, 0x61, 1];
        const ALIGN_LEFT = [ESC, 0x61, 0];
        const ALIGN_RIGHT = [ESC, 0x61, 2];
        const BOLD_ON = [ESC, 0x45, 1];
        const BOLD_OFF = [ESC, 0x45, 0];
        const DOUBLE_HEIGHT = [ESC, 0x21, 0x10];
        const NORMAL_TEXT = [ESC, 0x21, 0x00];
        const CUT_PAPER = [GS, 0x56, 0x41, 0x00];

        // Format helper functions
        const addCmd = (cmd) => bytes.push(...cmd);
        const addText = (text) => bytes.push(...encoder.encode(text));
        const addLine = (text) => addText(text + "\n");

        // 58mm printer usually has 32 characters per line
        const LINE_WIDTH = 32;

        const padLeftRight = (left, right) => {
            let spaces = LINE_WIDTH - left.length - right.length;
            if (spaces < 1) spaces = 1;
            return left + " ".repeat(spaces) + right;
        };

        // --- Start Building Receipt ---
        addCmd(INIT);

        // Header
        addCmd(ALIGN_CENTER);
        addCmd(BOLD_ON);
        addCmd(DOUBLE_HEIGHT);
        addLine(data.toko || "Toko Kita");
        addCmd(NORMAL_TEXT);
        addLine(data.alamat || "Alamat Toko");
        addLine(data.no_telp || "Nomor Telepon");
        addCmd(BOLD_OFF);
        addLine("--------------------------------");

        // Info
        addCmd(ALIGN_LEFT);
        addLine(`Tgl   : ${data.tanggal}`);
        addLine(`Kode  : ${data.kode_transaksi}`);
        addLine(`Bayar : ${data.jenis_pembayaran.toUpperCase()}`);
        addLine("--------------------------------");

        // Items
        data.items.forEach((item) => {
            addLine(item.nama);

            let formatHarga = new Intl.NumberFormat("id-ID").format(item.harga);
            let formatSub = new Intl.NumberFormat("id-ID").format(
                item.subtotal,
            );

            let qtyStr = `${item.qty} x ${formatHarga}`;
            addLine(padLeftRight(qtyStr, formatSub));
        });

        addLine("--------------------------------");

        // Totals
        addCmd(BOLD_ON);
        let formatTotal = new Intl.NumberFormat("id-ID").format(data.total);
        addLine(padLeftRight("TOTAL", formatTotal));
        addCmd(BOLD_OFF);

        let formatBayar = new Intl.NumberFormat("id-ID").format(data.bayar);
        addLine(padLeftRight("DIBAYAR", formatBayar));

        let formatKembali = new Intl.NumberFormat("id-ID").format(data.kembali);
        addLine(padLeftRight("KEMBALIAN", formatKembali));

        // Footer
        addCmd(ALIGN_CENTER);
        addLine("--------------------------------");
        addLine("Terima kasih atas");
        addLine("kunjungan Anda!");
        addLine("\n\n\n"); // feed paper

        return new Uint8Array(bytes);
    }

    async sendBytesInChunks(bytes) {
        // BLE usually has a 512 byte limit per characteristic write, sometimes 20.
        // We will use 512, if it fails on some devices, lower this to 20 or 100.
        const chunkSize = 256;

        for (let i = 0; i < bytes.length; i += chunkSize) {
            let chunk = bytes.slice(i, i + chunkSize);
            await this.characteristic.writeValue(chunk);
            // Small delay to prevent printer buffer overflow
            await new Promise((resolve) => setTimeout(resolve, 50));
        }
    }
}

// Make it globally available
window.PosPrinter = PosPrinter;
