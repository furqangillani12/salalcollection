@extends('layouts.admin')

@section('title', 'Yearly Attendance Report')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-2xl font-bold mb-4 md:mb-0">📆 Yearly Attendance Report</h2>
            <form method="GET" class="flex space-x-2">
                <input type="number" name="year" min="2020" max="{{ now()->format('Y') }}"
                       value="{{ $year }}"
                       class="border rounded px-3 py-2 shadow-sm focus:ring focus:ring-blue-200 w-32">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                    Generate
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-6">
                <h4 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                    Report for Year {{ $year }}
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-100 font-semibold">
                    <tr>
                        <th class="px-4 py-2">Month</th>
                        <th class="px-4 py-2 text-center">Working Days</th>
                        <th class="px-4 py-2 text-center">Present</th>
                        <th class="px-4 py-2 text-center">Late</th>
                        <th class="px-4 py-2 text-center">On Leave</th>
                        <th class="px-4 py-2">Attendance %</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($report as $month)
                        @php
                            $total = $month['present'] + $month['late'] + $month['on_leave'];
                            $attendancePercent = $month['working_days'] > 0
                                ? round(($month['present'] / $month['working_days']) * 100, 2)
                                : 0;
                            $progressColor = $attendancePercent > 90 ? 'bg-green-500' : ($attendancePercent > 70 ? 'bg-yellow-500' : 'bg-red-500');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3">{{ $month['name'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $month['working_days'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $month['present'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $month['late'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $month['on_leave'] }}</td>
                            <td class="px-4 py-3">
                                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div class="h-full {{ $progressColor }} text-white text-xs font-semibold text-center"
                                         style="width: {{ $attendancePercent }}%">
                                        {{ $attendancePercent }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                <canvas id="yearlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('yearlyChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($report, 'name')) !!},
                    datasets: [
                        {
                            label: 'Present',
                            data: {!! json_encode(array_column($report, 'present')) !!},
                            backgroundColor: 'rgba(34, 197, 94, 0.7)' // green
                        },
                        {
                            label: 'Late',
                            data: {!! json_encode(array_column($report, 'late')) !!},
                            backgroundColor: 'rgba(234, 179, 8, 0.7)' // yellow
                        },
                        {
                            label: 'On Leave',
                            data: {!! json_encode(array_column($report, 'on_leave')) !!},
                            backgroundColor: 'rgba(56, 189, 248, 0.7)' // blue
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ccc'
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                color: '#ccc'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                color: '#ccc'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
