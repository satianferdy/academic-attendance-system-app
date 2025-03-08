@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('page-title', 'Student Dashboard')

@section('sidebar')
    <a href="{{ route('student.dashboard') }}" class="sidebar-link active">Dashboard</a>
    <a href="#" class="sidebar-link">My Courses</a>
    <a href="#" class="sidebar-link">Attendance</a>
    <a href="#" class="sidebar-link">Face Registration</a>
    <a href="#" class="sidebar-link">Profile</a>
@endsection

@section('content')
    <div class="container-fluid">
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
@endsection

{{-- <div class="row mt-4"> --}}
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            Today's Schedule
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Course</th>
                        <th>Room</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>08:00 - 10:00</td>
                        <td>Introduction to Programming</td>
                        <td>Room 101</td>
                        <td><span class="badge bg-success">Attended</span></td>
                    </tr>
                    <tr>
                        <td>13:00 - 15:00</td>
                        <td>Database Systems</td>
                        <td>Room 203</td>
                        <td><span class="badge bg-warning">Upcoming</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="#" class="btn btn-primary btn-sm">Full Schedule</a>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            My Courses
        </div>
        <div class="card-body">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Introduction to Programming</h5>
                        <small class="text-success">92% attendance</small>
                    </div>
                    <p class="mb-1">Dr. John Smith</p>
                    <small>Monday, 08:00 - 10:00</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Database Systems</h5>
                        <small class="text-success">95% attendance</small>
                    </div>
                    <p class="mb-1">Dr. Emily Johnson</p>
                    <small>Wednesday, 13:00 - 15:00</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Web Development</h5>
                        <small class="text-warning">85% attendance</small>
                    </div>
                    <p class="mb-1">Prof. Michael Lee</p>
                    <small>Friday, 10:00 - 12:00</small>
                </a>
