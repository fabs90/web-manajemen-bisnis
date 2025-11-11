 <div id="main">
     <header class="mb-3">
         <a href="#" class="burger-btn d-block d-xl-none">
             <i class="bi bi-justify fs-3"></i>
         </a>
     </header>

     <div class="page-heading mb-2">
         <h3>
             @yield('section-heading')
         </h3>
     </div>
     <div class="page-content">
         <section class="row">
             <div class="col-12">
                 @yield('section-row')
             </div>
             {{-- Side content --}}
             {{-- <div class="col-12 col-lg-3">
                 @yield('section-side')
             </div> --}}
         </section>
     </div>
 </div>
