@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Manage User | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Manage User')
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
                <table class="table table-bordered" id="table-manage-user">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColor = match(strtolower($user->role)) {
                                        'superadmin' => 'bg-dark',
                                        'ukm' => 'bg-primary',
                                        'nelayan' => 'bg-info text-white',
                                        'koperasi' => 'bg-warning text-white',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $roleColor }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                @if($user->is_verified == 1)
                                    <span class="badge bg-success">Verified</span>
                                @else
                                    <span class="badge bg-danger">Unverified</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="{{route('superadmin.user-management.show', ['id' => $user->id])}}" class="btn btn-xs btn-info text-white" style="border-top-left-radius: 50rem; border-bottom-left-radius: 50rem; border-top-right-radius: 0; border-bottom-right-radius: 0; margin-right: 0;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('superadmin.user-management.destroy', ['id'=>$user->id]) }}" method="POST" class="form-delete m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" id="btn-delete" class="btn btn-xs btn-danger btn-delete" style="border-top-right-radius: 50rem; border-bottom-right-radius: 50rem; border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: 0;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
            $('#table-manage-user').DataTable({
                responsive: true,
                pageLength: 10,
            });

            // SweetAlert for Delete User
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data user ini akan dihapus secara permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
