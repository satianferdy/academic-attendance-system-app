@extends('layouts.app')

@section('title', 'Class Schedule Management')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Dosen</a></li>
            <li class="breadcrumb-item active" aria-current="page">Class Schedule</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Class Schedule Management</h6>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="scheduleTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab"
                                aria-controls="all" aria-selected="true">All Schedules</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="today-tab" data-bs-toggle="tab" href="#today" role="tab"
                                aria-controls="today" aria-selected="false">Today's Schedule</a>
                        </li>
                    </ul>
                    <!-- Tab content -->
                    <div class="tab-content border border-top-0 p-3" id="scheduleTabContent">
                        <!-- All Schedules Tab -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            <div class="table-responsive">
                                <table id="allScheduleTable" class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Course</th>
                                            <th>Class</th>
                                            <th>Room</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Semester/Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schedules as $key => $schedule)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <strong>{{ $schedule->course->code }}</strong><br>
                                                    <small>{{ $schedule->course->name }}</small>
                                                </td>
                                                <td>{{ $schedule->classroom->name }}</td>
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
                                                <td>{{ $schedule->semester }} / {{ $schedule->semesters->name }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No schedules found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <!-- Today's Schedule Tab -->
                        <div class="tab-pane fade" id="today" role="tabpanel" aria-labelledby="today-tab">
                            <div class="table-responsive">
                                <table id="todayScheduleTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Course</th>
                                            <th>Class</th>
                                            <th>Room</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $todayCount = 0; @endphp
                                        @forelse($schedules->where('day', now()->format('l')) as $key => $schedule)
                                            @php $todayCount++; @endphp
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <strong>{{ $schedule->course->code }}</strong><br>
                                                    <small>{{ $schedule->course->name }}</small>
                                                </td>
                                                <td>{{ $schedule->classroom->name }}</td>
                                                <td>{{ $schedule->room }}</td>
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No schedules for today</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#allScheduleTable').DataTable();
            $('#todayScheduleTable').DataTable();
        });
    </script>
@endpush
