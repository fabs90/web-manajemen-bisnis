        <script src="{{ asset('/dist/assets/static/js/components/dark.js') }}"></script>
        <script src="{{ asset('/dist/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('/dist/assets/compiled/js/app.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="{{ asset('./datatables/datatables.min.js') }}"></script>
        <script src="{{asset('./select2/select2.min.js')}}"></script>
        <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('/js/rupiah-helper.js') }}"></script>
        @stack('script')
        </body>

        </html>
