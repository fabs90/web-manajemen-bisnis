 <!-- Modal Fitur Terkunci (Upsell Modal) -->
 <div class="modal fade" id="featureLockedModal" tabindex="-1" aria-labelledby="featureLockedModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content border-0 shadow-lg text-center p-3">
             <div class="modal-body">
                 <!-- Icon Gembok / Mahkota -->
                 <div class="mb-3">
                     <span
                         class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-25 text-warning
  rounded-circle"
                         style="width: 72px; height: 72px;">
                         <i class="bi bi-lock-fill fs-1 text-warning"></i>
                     </span>
                 </div>

                 <!-- Judul Dinamis -->
                 <h4 class="fw-bold mb-2 text-dark" id="lockedFeatureTitle">Fitur Terkunci</h4>
                 <p class="text-muted small mb-4" id="lockedFeatureDesc">
                     Fitur ini memerlukan paket langganan aktif yang mendukung. Upgrade paket Anda sekarang untuk
                     membuka akses
                     penuh.
                 </p>

                 <!-- Tombol CTA -->
                 <div class="d-grid gap-2">
                     <a href="{{ route('subscriptions.index') }}" class="btn btn-primary btn-lg shadow-sm">
                         Lihat Paket & Upgrade
                     </a>
                     <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                         Nanti Saja
                     </button>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <script>
     document.addEventListener('DOMContentLoaded', function() {
         const modal = document.getElementById('featureLockedModal');
         if (modal) {
             modal.addEventListener('show.bs.modal', function(event) {
                 const triggerBtn = event.relatedTarget;
                 if (!triggerBtn) return;

                 const featureName = triggerBtn.getAttribute('data-feature-name') || 'Fitur Eksklusif';
                 const featureDesc = triggerBtn.getAttribute('data-feature-desc') ||
                     'Fitur ini belum tersedia pada paket Anda saat
                 ini.
                 ';

                 document.getElementById('lockedFeatureTitle').textContent = featureName;
                 document.getElementById('lockedFeatureDesc').textContent = featureDesc;
             });
         }
     });
 </script>
