@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@section('page-title', 'Lecturer Dashboard')

@section('sidebar')
    <a href="{{ route('lecturer.dashboard') }}" class="sidebar-link active">Dashboard</a>
    <a href="#" class="sidebar-link">My Courses</a>
    <a href="#" class="sidebar-link">Schedule</a>
    <a href="#" class="sidebar-link">Attendance</a>
    <a href="#" class="sidebar-link">Profile</a>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, {{ Auth::user()->name }}!</h5>
                        <p class="card-text">This is your lecturer dashboard. Here you can manage your courses, check
                            schedules, and monitor student attendance.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
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
                                    <small>Code: CS101</small>
                                </div>
                                <p class="mb-1">Monday, 08:00 - 10:00</p>
                                <small>40 students enrolled</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">Database Systems</h5>
                                    <small>Code: CS202</small>
                                </div>
                                <p class="mb-1">Wednesday, 13:00 - 15:00</p>
                                <small>35 students enrolled</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">Web Development</h5>
                                    <small>Code: CS301</small>
                                </div>
                                <p class="mb-1">Friday, 10:00 - 12:00</p>
                                <small>28 students enrolled</small>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary btn-sm">View All Courses</a>
                    </div>
                </div>
            </div>

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
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>08:00 - 10:00</td>
                                    <td>Introduction to Programming</td>
                                    <td>Room 101</td>
                                </tr>
                                <tr>
                                    <td>13:00 - 15:00</td>
                                    <td>Database Systems</td>
                                    <td>Room 203</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary btn-sm">Full Schedule</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Recent Attendance Records
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Course</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Percentage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Mar 7, 2025</td>
                                    <td>Introduction to Programming</td>
                                    <td>38</td>
                                    <td>2</td>
                                    <td>95%</td>
                                    <td><a href="#" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                                <tr>
                                    <td>Mar 6, 2025</td>
                                    <td>Database Systems</td>
                                    <td>32</td>
                                    <td>3</td>
                                    <td>91%</td>
                                    <td><a href="#" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                                <tr>
                                    <td>Mar 5, 2025</td>
                                    <td>Web Development</td>
                                    <td>25</td>
                                    <td>3</td>
                                    <td>89%</td>
                                    <td><a href="#" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
