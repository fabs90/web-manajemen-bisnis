        </div> <!-- end #app -->

        <script src="{{ asset('dist/assets/static/js/components/dark.js') }}"></script>
        <script src="{{ asset('dist/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('dist/assets/compiled/js/app.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous">
        </script>
        <script src="{{ asset('datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('select2/select2.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5"
            integrity="sha384-+xRXcGmExqvIzpl6UBfbrBkXyyxIDFnxQtfyoOiXSx0/ri19w6ifNhXjPLMxLwXM" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
            integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous">
        </script>
        <script src="{{ asset('dist/assets/rupiah-helper.js') }}"></script>
        <script src="{{ asset('js/sidebar-dashboard.js') }}"></script>
        <script src="{{ asset('js/kasir.js') }}"></script>
        @stack('script')
        </body>

        </html>
