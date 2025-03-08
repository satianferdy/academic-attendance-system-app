@extends('layouts.app')

@section('title', 'Student Dashboard')


{{-- @section('sidebar')
    <a href="{{ route('student.dashboard') }}" class="sidebar-link active">Dashboard</a>
    <a href="#" class="sidebar-link">My Courses</a>
    <a href="#" class="sidebar-link">Attendance</a>
    <a href="#" class="sidebar-link">Face Registration</a>
    <a href="#" class="sidebar-link">Profile</a>
@endsection --}}

@section('content')
    {{-- <div class="page-heading">
        <div class="page-title">
            <nav class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Akademik</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Jadwal</li>
                </ol>
            </nav>
        </div>
    </div> --}}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Welcome, {{ Auth::user()->name }}!</h5>
                            <p class="card-text">This is your student dashboard. Here you can view your courses, attendance
                                records, and manage your face recognition data.</p>

                            @if (!Auth::user()->student->face_registered)
                                <div class="alert alert-warning mt-3">
                                    <strong>Notice:</strong> You haven't registered your face data yet.
                                    <a href="#" class="alert-link">Click here to register now</a> for automated
                                    attendance.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Attendance Rate</h5>
                            <p class="card-text display-4">92%</p>
                            <p>Overall attendance rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Courses</h5>
                            <p class="card-text display-4">5</p>
                            <p>Enrolled courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Today</h5>
                            <p class="card-text display-4">2</p>
                            <p>Classes today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
