class PosPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.service = null;
        this.characteristic = null;

        // These are standard generic UUIDs for Chinese Thermal Printers.
        // Some use 'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
        // Default value for serviceUuid is the standard UUID, but we also include the alternative in optionalServices
        this.serviceUuid = '000018f0-0000-1000-8000-00805f9b34fb';
        this.characteristicUuid = '00002af1-0000-1000-8000-00805f9b34fb';
    }

    async connect() {
        if (!navigator.bluetooth) {
            alert('Browser ini tidak mendukung Web Bluetooth API. Silakan gunakan Google Chrome di PC/Android.');
            return false;
        }

        try {
            console.log('Meminta akses Bluetooth...');
            this.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: [this.serviceUuid] }
                ],
                optionalServices: ['e7810a71-73ae-499d-8c15-faa9aef0c3f2'] // Alternative common service
            });

            console.log('Menghubungkan ke GATT Server...');
            this.server = await this.device.gatt.connect();

            console.log('Mencari Bluetooth Service...');
            try {
                this.service = await this.server.getPrimaryService(this.serviceUuid);
            } catch (e) {
                // Try alternative if first fails
                this.service = await this.server.getPrimaryService('e7810a71-73ae-499d-8c15-faa9aef0c3f2');
            }

            console.log('Mencari Characteristic...');
            try {
                this.characteristic = await this.service.getCharacteristic(this.characteristicUuid);
            } catch (e) {
                // Try alternative characteristic if first fails
                this.characteristic = await this.service.getCharacteristic('bef8d6c9-9c21-4c9e-b632-bd58c1009f9f');
            }

            console.log('Printer berhasil terhubung!');
            return true;

        } catch (error) {
            console.error('Koneksi Bluetooth gagal:', error);
            alert('Gagal terhubung ke printer: ' + error.message);
            return false;
        }
    }

    async printReceipt(receiptData) {
        if (!this.characteristic) {
            const connected = await this.connect();
            if (!connected) return;
        }

        try {
            let bytes = this.buildReceiptBytes(receiptData);
            await this.sendBytesInChunks(bytes);
            console.log('Pencetakan selesai!');
        } catch (error) {
            console.error('Gagal mencetak:', error);
            alert('Gagal mencetak: ' + error.message);
        }
    }

    buildReceiptBytes(data) {
        let encoder = new TextEncoder();
        let bytes = [];

        // ESC/POS Commands
        const ESC = 0x1B;
        const GS = 0x1D;
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
        const addLine = (text) => addText(text + '\n');

        // 58mm printer usually has 32 characters per line
        const LINE_WIDTH = 32;

        const padLeftRight = (left, right) => {
            let spaces = LINE_WIDTH - left.length - right.length;
            if (spaces < 1) spaces = 1;
            return left + ' '.repeat(spaces) + right;
        };

        // --- Start Building Receipt ---
        addCmd(INIT);

        // Header
        addCmd(ALIGN_CENTER);
        addCmd(BOLD_ON);
        addCmd(DOUBLE_HEIGHT);
        addLine(data.toko || 'Toko Kita');
        addCmd(NORMAL_TEXT);
        addCmd(BOLD_OFF);
        addLine('--------------------------------');

        // Info
        addCmd(ALIGN_LEFT);
        addLine(`Tgl   : ${data.tanggal}`);
        addLine(`Kasir : ${data.kasir}`);
        addLine(`Trx   : ${data.kode_transaksi}`);
        addLine(`Bayar : ${data.jenis_pembayaran.toUpperCase()}`);
        addLine('--------------------------------');

        // Items
        data.items.forEach(item => {
            addLine(item.nama);

            let formatHarga = new Intl.NumberFormat('id-ID').format(item.harga);
            let formatSub = new Intl.NumberFormat('id-ID').format(item.subtotal);

            let qtyStr = `${item.qty} x ${formatHarga}`;
            addLine(padLeftRight(qtyStr, formatSub));
        });

        addLine('--------------------------------');

        // Totals
        addCmd(BOLD_ON);
        let formatTotal = new Intl.NumberFormat('id-ID').format(data.total);
        addLine(padLeftRight('TOTAL', formatTotal));
        addCmd(BOLD_OFF);

        let formatBayar = new Intl.NumberFormat('id-ID').format(data.bayar);
        addLine(padLeftRight('DIBAYAR', formatBayar));

        let formatKembali = new Intl.NumberFormat('id-ID').format(data.kembali);
        addLine(padLeftRight('KEMBALI', formatKembali));

        // Footer
        addCmd(ALIGN_CENTER);
        addLine('--------------------------------');
        addLine('Terima kasih atas');
        addLine('kunjungan Anda!');
        addLine('\n\n\n'); // feed paper

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
            await new Promise(resolve => setTimeout(resolve, 50));
        }
    }
}

// Make it globally available
window.PosPrinter = PosPrinter;
