@extends('layouts.app')

@section('title', 'Class Schedule Details')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Class Schedule</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0">Class Schedule Details</h6>
                        <div>
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}"
                                class="btn btn-primary btn-icon-text">
                                <i class="btn-icon-prepend" data-feather="edit"></i>
                                Edit
                            </a>
                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="post"
                                class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger btn-icon-text"
                                    onclick="return confirm('Are you sure you want to delete this schedule?')">
                                    <i class="btn-icon-prepend" data-feather="trash-2"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Course Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Course Code</th>
                                            <td>{{ $schedule->course_code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Course Name</th>
                                            <td>{{ $schedule->course_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Semester</th>
                                            <td>{{ $schedule->semester }}</td>
                                        </tr>
                                        <tr>
                                            <th>Academic Year</th>
                                            <td>{{ $schedule->academic_year }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Schedule Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Lecturer</th>
                                            <td>{{ $schedule->lecturer->user->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Room</th>
                                            <td>{{ $schedule->room }}</td>
                                        </tr>
                                        <tr>
                                            <th>Day</th>
                                            <td>{{ $schedule->day }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $schedule->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Update</th>
                                            <td>{{ $schedule->updated_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Time Slots ({{ $schedule->timeSlots->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    @if ($schedule->timeSlots->count() > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($schedule->timeSlots as $timeSlot)
                                                <div class="badge bg-primary p-2">
                                                    {{ $timeSlot->start_time->format('H:i') }} -
                                                    {{ $timeSlot->end_time->format('H:i') }}
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-4">
                                            <div class="card mb-3">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Start Time</th>
                                                                    <th>End Time</th>
                                                                    <th>Duration</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($schedule->timeSlots as $index => $timeSlot)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $timeSlot->start_time->format('H:i') }}</td>
                                                                        <td>{{ $timeSlot->end_time->format('H:i') }}</td>
                                                                        <td>
                                                                            @php
                                                                                $start = \Carbon\Carbon::parse(
                                                                                    $timeSlot->start_time,
                                                                                );
                                                                                $end = \Carbon\Carbon::parse(
                                                                                    $timeSlot->end_time,
                                                                                );
                                                                                $durationInMinutes = $end->diffInMinutes(
                                                                                    $start,
                                                                                );
                                                                                echo floor($durationInMinutes / 60) .
                                                                                    ' hr ' .
                                                                                    $durationInMinutes % 60 .
                                                                                    ' min';
                                                                            @endphp
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            No time slots have been assigned to this schedule.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
