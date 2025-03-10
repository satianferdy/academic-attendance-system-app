@extends('layouts.app')

@section('title', 'Class Schedule Management')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Class Schedule</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Class Schedule Management</h6>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mb-3">
                        <div>
                            <a href="{{ route('admin.schedules.create') }}"
                                class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                                <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                Add Schedule
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Course</th>
                                    <th>Lecturer</th>
                                    <th>Room</th>
                                    <th>Day</th>
                                    <th>Time Slots</th>
                                    <th>Semester/Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $key => $schedule)
                                    <tr>
                                        <td>{{ $schedules->firstItem() + $key }}</td>
                                        <td>
                                            <strong>{{ $schedule->course_code }}</strong><br>
                                            <small>{{ $schedule->course_name }}</small>
                                        </td>
                                        <td>{{ $schedule->lecturer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $schedule->room }}</td>
                                        <td>{{ $schedule->day }}</td>
                                        <td>
                                            @if ($schedule->timeSlots->count() > 0)
                                                @foreach ($schedule->timeSlots as $timeSlot)
                                                    <div>{{ $timeSlot->start_time->format('H:i') }} -
                                                        {{ $timeSlot->end_time->format('H:i') }}</div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No time slots</span>
                                            @endif
                                        </td>
                                        <td>{{ $schedule->semester }} / {{ $schedule->academic_year }}</td>
                                        <td>
                                            <a href="{{ route('admin.schedules.show', $schedule->id) }}"
                                                class="btn btn-sm btn-info btn-icon">
                                                <i class="btn-icon-prepend" data-feather="eye"></i>
                                            </a>
                                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                                class="btn btn-sm btn-primary btn-icon">
                                                <i class="btn-icon-prepend" data-feather="check-square"></i>
                                            </a>
                                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}"
                                                method="post" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-delete btn-sm btn-danger btn-icon"
                                                    onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                    <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No schedules found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
