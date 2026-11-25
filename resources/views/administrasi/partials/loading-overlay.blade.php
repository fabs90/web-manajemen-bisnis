<div id="administrasi-loading-overlay"
    class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"
    style="z-index: 9999;">
    <div class="d-flex flex-column justify-content-center align-items-center h-100 px-3">
        <div class="card shadow-lg border-0 text-center p-4" style="max-width: 360px;">
            <div class="card-body">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="fw-bold mb-1" id="administrasi-loading-title">Sedang Memproses Data...</h5>
                <p class="text-muted small mb-0" id="administrasi-loading-message">
                    Mohon tunggu, permintaan administrasi Anda sedang diproses.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const overlay = document.getElementById('administrasi-loading-overlay');
        const titleEl = document.getElementById('administrasi-loading-title');
        const messageEl = document.getElementById('administrasi-loading-message');
        const defaultTitle = 'Sedang Memproses Data...';
        const defaultMessage = 'Mohon tunggu, permintaan administrasi Anda sedang diproses.';
        const uploadMessage = 'Mohon tunggu, sedang memproses data dan mengunggah file/gambar.';

        let submittingForms = new WeakSet();

        window.showAdministrasiLoading = function(title, message) {
            if (!overlay) return;
            if (titleEl) titleEl.textContent = title || defaultTitle;
            if (messageEl) messageEl.textContent = message || defaultMessage;
            overlay.classList.remove('d-none');
        };

        window.hideAdministrasiLoading = function() {
            if (!overlay) return;
            overlay.classList.add('d-none');
        };

        function shouldShowLoadingForForm(form) {
            if (!form || form.tagName !== 'FORM') return false;
            if (form.hasAttribute('data-no-loading')) return false;
            if ((form.getAttribute('target') || '').toLowerCase() === '_blank') return false;
            if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return false;
            return true;
        }

        function hasMultipartOrFiles(form) {
            const enctype = (form.getAttribute('enctype') || '').toLowerCase();
            if (enctype === 'multipart/form-data') return true;
            const fileInputs = form.querySelectorAll('input[type="file"]');
            for (const input of fileInputs) {
                if (input.files && input.files.length > 0) return true;
            }
            return false;
        }

        function disableSubmitControls(form) {
            const controls = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            controls.forEach(function(control) {
                if (!control.disabled) {
                    control.disabled = true;
                    control.setAttribute('data-loading-disabled', 'true');
                }
            });
        }

        document.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return;
            const form = e.target;
            if (!shouldShowLoadingForForm(form)) return;

            if (submittingForms.has(form)) {
                e.preventDefault();
                return;
            }

            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                return;
            }

            submittingForms.add(form);
            setTimeout(function() {
                disableSubmitControls(form);
            }, 0);

            const msg = hasMultipartOrFiles(form) ? uploadMessage : defaultMessage;
            window.showAdministrasiLoading(defaultTitle, msg);
        });

        const nativeSubmit = HTMLFormElement.prototype.submit;
        HTMLFormElement.prototype.submit = function() {
            if (shouldShowLoadingForForm(this)) {
                const msg = hasMultipartOrFiles(this) ? uploadMessage : defaultMessage;
                window.showAdministrasiLoading(defaultTitle, msg);
            }
            return nativeSubmit.apply(this, arguments);
        };

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                submittingForms = new WeakSet();
                document.querySelectorAll('[data-loading-disabled="true"]').forEach(function(el) {
                    el.disabled = false;
                    el.removeAttribute('data-loading-disabled');
                });
                window.hideAdministrasiLoading();
            }
        });
    })();
</script>
