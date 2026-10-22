    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>@yield('auth-title')</h1>
                <p>@yield('auth-subtitle')</p>
            </div>

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger">
                    <ul style="padding-left: 20px; margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert danger">{{ session('error') }}</div>
            @endif

            @yield('auth-content')
        </div>
    </div>
