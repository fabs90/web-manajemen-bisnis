@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Verify Account | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Verify Account')
@section('section-row')

    <table class="table">
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
                    @if($user->is_verified != 1)
                        <a href="#" class="btn btn-primary">Verify</a>
                    @else
                        <span class="badge bg-success">Verified</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
