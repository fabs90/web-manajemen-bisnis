@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Verify Account | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Verify Account')
@section('section-row')
    {{-- Alert sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Sukses!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Alert Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table-verify-user">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td>
                                    @if ($user->is_verified != 1)
                                        <form action="{{ route('superadmin.verify-account.verify', $user->id) }}"
                                            method="POST" class="d-inline m-0 p-0">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Verify</button>
                                        </form>
                                    @else
                                        <span class="badge bg-success">Verified</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#table-verify-user').DataTable({
                responsive: true,
                pageLength: 10,
            });
        })
    </script>
@endpush
