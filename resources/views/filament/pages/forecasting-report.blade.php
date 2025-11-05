<x-filament::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Stok Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Bahan Baku</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summaryData['stok']['total_bahan'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <x-heroicon-s-cube class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                @if($summaryData['stok']['stok_rendah'] > 0 || $summaryData['stok']['stok_habis'] > 0)
                <div class="mt-4 flex space-x-4 text-xs">
                    @if($summaryData['stok']['stok_rendah'] > 0)
                        <span class="text-red-600 dark:text-red-400">{{ $summaryData['stok']['stok_rendah'] }} Stok Rendah</span>
                    @endif
                    @if($summaryData['stok']['stok_habis'] > 0)
                        <span class="text-orange-600 dark:text-orange-400">{{ $summaryData['stok']['stok_habis'] }} Stok Habis</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Pembelian Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total PO Pembelian</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summaryData['pembelian']['total_po'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <x-heroicon-s-shopping-cart class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
                @if($summaryData['pembelian']['bulan_ini'] > 0 || $summaryData['pembelian']['pending'] > 0)
                <div class="mt-4 flex space-x-4 text-xs">
                    @if($summaryData['pembelian']['bulan_ini'] > 0)
                        <span class="text-blue-600 dark:text-blue-400">{{ $summaryData['pembelian']['bulan_ini'] }} Bulan Ini</span>
                    @endif
                    @if($summaryData['pembelian']['pending'] > 0)
                        <span class="text-yellow-600 dark:text-yellow-400">{{ $summaryData['pembelian']['pending'] }} Pending</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Produksi Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Produksi</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summaryData['produksi']['total_produksi'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <x-heroicon-s-cog class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                @if($summaryData['produksi']['selesai_bulan_ini'] > 0 || $summaryData['produksi']['sedang_proses'] > 0)
                <div class="mt-4 flex space-x-4 text-xs">
                    @if($summaryData['produksi']['selesai_bulan_ini'] > 0)
                        <span class="text-green-600 dark:text-green-400">{{ $summaryData['produksi']['selesai_bulan_ini'] }} Selesai</span>
                    @endif
                    @if($summaryData['produksi']['sedang_proses'] > 0)
                        <span class="text-blue-600 dark:text-blue-400">{{ $summaryData['produksi']['sedang_proses'] }} Proses</span>
                    @endif
                </div>
                @endif
            </div>

            <!-- Penjualan Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Penjualan</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summaryData['penjualan']['total_penjualan']) }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                        <x-heroicon-s-chart-bar class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                </div>
                <div class="mt-4 flex space-x-4 text-xs">
                    @if($summaryData['penjualan']['bulan_ini'] > 0)
                        <span class="text-green-600 dark:text-green-400">{{ number_format($summaryData['penjualan']['bulan_ini']) }} Bulan Ini</span>
                    @endif
                    <span class="{{ $summaryData['penjualan']['pertumbuhan'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $summaryData['penjualan']['pertumbuhan'] }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Trend Penjualan 6 Bulan</h2>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full"></span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Penjualan</span>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Inventory Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status Stok Bahan Baku</h2>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Stok</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Minimal</span>
                        </div>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>

            <!-- Movement Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 lg:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Operasional</h2>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-1">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Penjualan</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Pembelian</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Produksi</span>
                        </div>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="movementChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Forecasting Results -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Hasil Peramalan -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hasil Peramalan Permintaan</h2>
                <div class="space-y-4">
                    @foreach($forecastResults as $forecast)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md dark:hover:shadow-gray-900/50 transition-shadow">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $forecast['product'] }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $forecast['trend'] == 'naik' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800' :
                                       ($forecast['trend'] == 'turun' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-600') }}">
                                    {{ $forecast['trend'] == 'naik' ? '📈 Naik' :
                                       ($forecast['trend'] == 'turun' ? '📉 Turun' : '➡ Stabil') }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">3 Bulan Terakhir:</span>
                                    <span class="font-medium dark:text-gray-200">{{ $forecast['last_3_months'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Rata-rata Bergerak:</span>
                                    <span class="font-medium dark:text-gray-200">{{ $forecast['moving_average'] }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-600 dark:text-gray-400">Prediksi Bulan Depan:</span>
                                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ $forecast['next_month_forecast'] }} unit</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Rekomendasi Pembelian -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Rekomendasi Pembelian Bahan Baku</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($purchaseSuggestions as $suggestion)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:shadow-sm dark:hover:shadow-gray-900/50 transition-shadow">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white text-sm">{{ $suggestion['product'] }}</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $suggestion['raw_material'] }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $suggestion['priority'] == 'Tinggi' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-800' :
                                       ($suggestion['priority'] == 'Sedang' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800') }}">
                                    {{ $suggestion['priority'] }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Butuh:</span>
                                    <span class="dark:text-gray-200">{{ $suggestion['required_qty'] }} {{ $suggestion['unit'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Stok:</span>
                                    <span class="dark:text-gray-200">{{ $suggestion['current_stock'] }} {{ $suggestion['unit'] }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-600 dark:text-gray-400">Rekomendasi Beli:</span>
                                    <span class="font-medium dark:text-gray-200">{{ $suggestion['suggested_purchase'] }} {{ $suggestion['unit'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if(count($purchaseSuggestions) == 0)
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada rekomendasi pembelian saat ini</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detect dark mode
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#e5e7eb' : '#6b7280';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            // Gradient function for charts
            const createGradient = (ctx, color1, color2) => {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color1);
                gradient.addColorStop(1, color2);
                return gradient;
            };

            // Sales Chart - Modern Line Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: @json($salesChartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.4,
                            borderWidth: 3,
                            borderColor: '#4f46e5'
                        },
                        point: {
                            radius: 5,
                            backgroundColor: '#4f46e5',
                            borderColor: 'white',
                            borderWidth: 2,
                            hoverRadius: 7
                        }
                    }
                }
            });

            // Inventory Chart - Modern Bar Chart
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            new Chart(inventoryCtx, {
                type: 'bar',
                data: {
                    ...@json($inventoryChartData),
                    datasets: @json($inventoryChartData).datasets.map((dataset, index) => ({
                        ...dataset,
                        backgroundColor: index === 0
                            ? 'rgba(34, 197, 94, 0.7)'
                            : 'rgba(239, 68, 68, 0.7)',
                        borderColor: index === 0 ? '#10b981' : '#ef4444',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: textColor,
                                maxRotation: 45
                            }
                        }
                    }
                }
            });

            // Movement Chart - Multi Line Chart
            const movementCtx = document.getElementById('movementChart').getContext('2d');
            new Chart(movementCtx, {
                type: 'line',
                data: {
                    ...@json($movementChartData),
                    datasets: @json($movementChartData).datasets.map((dataset, index) => {
                        const colors = [
                            { bg: 'rgba(79, 70, 229, 0.1)', border: '#4f46e5' },
                            { bg: 'rgba(16, 185, 129, 0.1)', border: '#10b981' },
                            { bg: 'rgba(245, 158, 11, 0.1)', border: '#f59e0b' }
                        ];
                        return {
                            ...dataset,
                            backgroundColor: colors[index].bg,
                            borderColor: colors[index].border,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6,
                            backgroundColor: 'white',
                            borderWidth: 2
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament::page>
