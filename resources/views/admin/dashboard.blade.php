@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link active">Dashboard</a>
    <a href="#" class="sidebar-link">Users</a>
    <a href="#" class="sidebar-link">Courses</a>
    <a href="#" class="sidebar-link">Reports</a>
    <a href="#" class="sidebar-link">Settings</a>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, Admin!</h5>
                        <p class="card-text">This is your administration dashboard. From here you can manage users, courses,
                            and system settings.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text display-4">42</p>
                        <p>Total registered users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Courses</h5>
                        <p class="card-text display-4">15</p>
                        <p>Active courses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Faculties</h5>
                        <p class="card-text display-4">5</p>
                        <p>Academic faculties</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Recent Activities
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">New user registered (student)</li>
                            <li class="list-group-item">New course added: Introduction to Programming</li>
                            <li class="list-group-item">User profile updated</li>
                            <li class="list-group-item">New user registered (lecturer)</li>
                            <li class="list-group-item">System backup completed</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        System Status
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Database</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 25%">25%</div>
                            </div>
                            <small class="text-muted">Database usage</small>
                        </div>
                        <div class="mb-3">
                            <label>Storage</label>
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 40%">40%</div>
                            </div>
                            <small class="text-muted">Storage usage</small>
                        </div>
                        <div>
                            <label>API Rate Limit</label>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 70%">70%</div>
                            </div>
                            <small class="text-muted">API usage</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
