<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DI vs Non-DI Performance Comparison Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f7fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 2.2em;
        }

        .header p {
            color: #7f8c8d;
            margin: 8px 0 0 0;
            font-size: 1em;
        }

        /* Compact Metric Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .metric-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .metric-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .metric-card h3 {
            margin: 0 0 10px 0;
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .metric-card h3 span {
            margin-right: 6px;
        }

        .metric-values {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .metric-value {
            text-align: center;
            flex: 1;
        }

        .metric-value .label {
            font-size: 0.75em;
            opacity: 0.9;
        }

        .metric-value .value {
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 3px;
        }

        .improvement {
            background: rgba(255, 255, 255, 0.25);
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
            margin-top: 8px;
            font-size: 0.85em;
            font-weight: bold;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 25px 0;
        }

        .chart-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        .chart-container h4 {
            text-align: center;
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1em;
        }

        .chart-container canvas {
            max-height: 200px;
        }

        .summary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
        }

        .summary h3 {
            margin: 0 0 12px 0;
            font-size: 1.2em;
        }

        .summary ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .summary li {
            margin: 5px 0;
            font-size: 0.95em;
        }

        .complexity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 8px;
        }

        .complexity-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px;
            border-radius: 4px;
            text-align: center;
            font-size: 0.85em;
        }

        .overall-score {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #2c3e50;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            text-align: center;
        }

        .score-number {
            font-size: 2.2em;
            font-weight: bold;
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🚀 DI vs Non-DI Performance Report</h1>
            <p>Academic Attendance System Testing Results</p>
            <p><strong>Generated:</strong> {{ $data['timestamp'] }} | <strong>Iterations:</strong>
                {{ $data['test_iterations'] }}</p>
        </div>

        @if (isset($data['overall_improvement']))
            <div class="overall-score">
                <h3>🎯 Overall DI Performance Improvement</h3>
                <div class="score-number">{{ number_format($data['overall_improvement'], 1) }}%</div>
                <p>Average improvement across all measured metrics</p>
            </div>
        @endif

        <div class="metrics-grid">
            @if (isset($data['metrics']['setup_time']))
                <div class="metric-card success">
                    <h3><span>⚡</span>Setup Time</h3>
                    <div class="metric-values">
                        <div class="metric-value">
                            <div class="label">DI</div>
                            <div class="value">{{ number_format($data['metrics']['setup_time']['di_avg'] ?? 0, 2) }}ms
                            </div>
                        </div>
                        <div class="metric-value">
                            <div class="label">Non-DI</div>
                            <div class="value">
                                {{ number_format($data['metrics']['setup_time']['non_di_avg'] ?? 0, 2) }}ms</div>
                        </div>
                    </div>
                    <div class="improvement">
                        {{ number_format($data['metrics']['setup_time']['improvement_percentage'] ?? 0, 1) }}% Faster
                    </div>
                </div>
            @endif

            @if (isset($data['metrics']['execution_time']))
                <div class="metric-card success">
                    <h3><span>🏃</span>Execution Time</h3>
                    <div class="metric-values">
                        <div class="metric-value">
                            <div class="label">DI</div>
                            <div class="value">
                                {{ number_format($data['metrics']['execution_time']['di_avg'] ?? 0, 2) }}ms</div>
                        </div>
                        <div class="metric-value">
                            <div class="label">Non-DI</div>
                            <div class="value">
                                {{ number_format($data['metrics']['execution_time']['non_di_avg'] ?? 0, 2) }}ms</div>
                        </div>
                    </div>
                    <div class="improvement">
                        {{ number_format($data['metrics']['execution_time']['improvement_percentage'] ?? 0, 1) }}%
                        Faster</div>
                </div>
            @endif

            @if (isset($data['metrics']['lines_of_code']))
                <div class="metric-card success">
                    <h3><span>📝</span>Code Lines</h3>
                    <div class="metric-values">
                        <div class="metric-value">
                            <div class="label">DI</div>
                            <div class="value">{{ $data['metrics']['lines_of_code']['di_lines'] ?? 0 }}</div>
                        </div>
                        <div class="metric-value">
                            <div class="label">Non-DI</div>
                            <div class="value">{{ $data['metrics']['lines_of_code']['non_di_lines'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="improvement">
                        {{ number_format($data['metrics']['lines_of_code']['reduction_percentage'] ?? 0, 1) }}% Less
                        Code</div>
                </div>
            @endif

            @if (isset($data['metrics']['memory_usage']))
                <div class="metric-card success">
                    <h3><span>💾</span>Memory Usage</h3>
                    <div class="metric-values">
                        <div class="metric-value">
                            <div class="label">DI</div>
                            <div class="value">
                                {{ number_format(($data['metrics']['memory_usage']['di_avg'] ?? 0) / 1024, 2) }}KB
                            </div>
                        </div>
                        <div class="metric-value">
                            <div class="label">Non-DI</div>
                            <div class="value">
                                {{ number_format(($data['metrics']['memory_usage']['non_di_avg'] ?? 0) / 1024, 2) }}KB
                            </div>
                        </div>
                    </div>
                    <div class="improvement">
                        {{ number_format($data['metrics']['memory_usage']['improvement_percentage'] ?? 0, 1) }}% Less
                        Memory</div>
                </div>
            @endif

            @if (isset($data['metrics']['complexity']))
                <div class="metric-card">
                    <h3><span>🧩</span>Complexity</h3>
                    <div class="complexity-grid">
                        <div class="complexity-item">
                            <div class="label">DI Cyclomatic</div>
                            <div class="value">
                                {{ $data['metrics']['complexity']['di_complexity']['cyclomatic_complexity'] ?? 0 }}
                            </div>
                        </div>
                        <div class="complexity-item">
                            <div class="label">Non-DI Cyclomatic</div>
                            <div class="value">
                                {{ $data['metrics']['complexity']['non_di_complexity']['cyclomatic_complexity'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                    <div class="improvement">Lower = Better</div>
                </div>
            @endif

            @if (isset($data['metrics']['reliability']))
                <div
                    class="metric-card {{ $data['metrics']['reliability']['di_success_rate'] > $data['metrics']['reliability']['non_di_success_rate'] ? 'success' : 'warning' }}">
                    <h3><span>🎯</span>Reliability</h3>
                    <div class="metric-values">
                        <div class="metric-value">
                            <div class="label">DI Success</div>
                            <div class="value">
                                {{ number_format($data['metrics']['reliability']['di_success_rate'] ?? 0, 1) }}%</div>
                        </div>
                        <div class="metric-value">
                            <div class="label">Non-DI Success</div>
                            <div class="value">
                                {{ number_format($data['metrics']['reliability']['non_di_success_rate'] ?? 0, 1) }}%
                            </div>
                        </div>
                    </div>
                    <div class="improvement">
                        {{ $data['metrics']['reliability']['di_success_rate'] > $data['metrics']['reliability']['non_di_success_rate'] ? 'DI More Reliable' : 'Reliability Issues' }}
                    </div>
                </div>
            @endif
        </div>

        <div class="charts-grid">
            @if (isset($data['metrics']['setup_time']))
                <div class="chart-container">
                    <h4>Setup Time Comparison</h4>
                    <canvas id="setupChart"></canvas>
                </div>
            @endif

            @if (isset($data['metrics']['execution_time']))
                <div class="chart-container">
                    <h4>Execution Time Comparison</h4>
                    <canvas id="executionChart"></canvas>
                </div>
            @endif

            @if (isset($data['metrics']['lines_of_code']))
                <div class="chart-container">
                    <h4>Lines of Code Comparison</h4>
                    <canvas id="linesChart"></canvas>
                </div>
            @endif

            @if (isset($data['metrics']['memory_usage']))
                <div class="chart-container">
                    <h4>Memory Usage Comparison</h4>
                    <canvas id="memoryChart"></canvas>
                </div>
            @endif

            @if (isset($data['metrics']['complexity']))
                <div class="chart-container">
                    <h4>Complexity Analysis</h4>
                    <canvas id="complexityChart"></canvas>
                </div>
            @endif

            @if (isset($data['metrics']['reliability']))
                <div class="chart-container">
                    <h4>Reliability Comparison</h4>
                    <canvas id="reliabilityChart"></canvas>
                </div>
            @endif
        </div>

        <div class="summary">
            <h3>🎯 Executive Summary</h3>
            <p><strong>✅ Dependency Injection (DI) significantly outperforms Non-DI approach:</strong></p>
            <ul>
                <li><strong>Performance:</strong>
                    {{ number_format($data['metrics']['execution_time']['improvement_percentage'] ?? 0, 1) }}% faster
                    execution, {{ number_format($data['metrics']['setup_time']['improvement_percentage'] ?? 0, 1) }}%
                    faster setup</li>
                <li><strong>Code Quality:</strong>
                    {{ number_format($data['metrics']['lines_of_code']['reduction_percentage'] ?? 0, 1) }}% less code,
                    reduced complexity</li>
                <li><strong>Resource Efficiency:</strong>
                    {{ number_format($data['metrics']['memory_usage']['improvement_percentage'] ?? 0, 1) }}% less
                    memory usage</li>
                <li><strong>Reliability:</strong>
                    {{ number_format($data['metrics']['reliability']['di_success_rate'] ?? 0, 1) }}% success rate vs
                    {{ number_format($data['metrics']['reliability']['non_di_success_rate'] ?? 0, 1) }}% for Non-DI
                </li>
            </ul>
            <p><strong>🎯 Recommendation:</strong> Adopt Dependency Injection for improved maintainability, performance,
                and testing reliability.</p>
        </div>
    </div>

    <script>
        const metricsData = {!! $metricsJson !!};
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        // Setup Time Chart
        @if (isset($data['metrics']['setup_time']))
            new Chart(document.getElementById('setupChart'), {
                type: 'bar',
                data: {
                    labels: ['DI', 'Non-DI'],
                    datasets: [{
                        data: [metricsData.setup_time?.di_avg || 0, metricsData.setup_time?.non_di_avg ||
                            0
                        ],
                        backgroundColor: ['rgba(76, 175, 80, 0.8)', 'rgba(244, 67, 54, 0.8)']
                    }]
                },
                options: chartOptions
            });
        @endif

        // Execution Time Chart
        @if (isset($data['metrics']['execution_time']))
            new Chart(document.getElementById('executionChart'), {
                type: 'bar',
                data: {
                    labels: ['DI', 'Non-DI'],
                    datasets: [{
                        data: [metricsData.execution_time?.di_avg || 0, metricsData.execution_time
                            ?.non_di_avg || 0
                        ],
                        backgroundColor: ['rgba(76, 175, 80, 0.8)', 'rgba(244, 67, 54, 0.8)']
                    }]
                },
                options: chartOptions
            });
        @endif

        // Lines of Code Chart
        @if (isset($data['metrics']['lines_of_code']))
            new Chart(document.getElementById('linesChart'), {
                type: 'bar',
                data: {
                    labels: ['DI', 'Non-DI'],
                    datasets: [{
                        data: [metricsData.lines_of_code?.di_lines || 0, metricsData.lines_of_code
                            ?.non_di_lines || 0
                        ],
                        backgroundColor: ['rgba(76, 175, 80, 0.8)', 'rgba(244, 67, 54, 0.8)']
                    }]
                },
                options: chartOptions
            });
        @endif

        // Memory Usage Chart
        @if (isset($data['metrics']['memory_usage']))
            new Chart(document.getElementById('memoryChart'), {
                type: 'bar',
                data: {
                    labels: ['DI', 'Non-DI'],
                    datasets: [{
                        data: [(metricsData.memory_usage?.di_avg || 0) / 1024, (metricsData.memory_usage
                            ?.non_di_avg || 0) / 1024],
                        backgroundColor: ['rgba(76, 175, 80, 0.8)', 'rgba(244, 67, 54, 0.8)']
                    }]
                },
                options: chartOptions
            });
        @endif

        // Complexity Chart
        @if (isset($data['metrics']['complexity']))
            new Chart(document.getElementById('complexityChart'), {
                type: 'radar',
                data: {
                    labels: ['Cyclomatic', 'Cognitive'],
                    datasets: [{
                            label: 'DI',
                            data: [metricsData.complexity?.di_complexity?.cyclomatic_complexity || 0,
                                metricsData.complexity?.di_complexity?.cognitive_complexity || 0
                            ],
                            backgroundColor: 'rgba(76, 175, 80, 0.2)',
                            borderColor: 'rgba(76, 175, 80, 1)'
                        },
                        {
                            label: 'Non-DI',
                            data: [metricsData.complexity?.non_di_complexity?.cyclomatic_complexity || 0,
                                metricsData.complexity?.non_di_complexity?.cognitive_complexity || 0
                            ],
                            backgroundColor: 'rgba(244, 67, 54, 0.2)',
                            borderColor: 'rgba(244, 67, 54, 1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif

        // Reliability Chart
        @if (isset($data['metrics']['reliability']))
            new Chart(document.getElementById('reliabilityChart'), {
                type: 'doughnut',
                data: {
                    labels: ['DI Success', 'Non-DI Success'],
                    datasets: [{
                        data: [metricsData.reliability?.di_success_rate || 0, metricsData.reliability
                            ?.non_di_success_rate || 0
                        ],
                        backgroundColor: ['rgba(76, 175, 80, 0.8)', 'rgba(244, 67, 54, 0.8)']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        @endif
    </script>
</body>

</html>
