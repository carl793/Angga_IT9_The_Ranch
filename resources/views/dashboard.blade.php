@extends('layouts.farm_app')
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Farm Analytics Overview</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Total Products</p>
            <p class="text-2xl font-black">{{ $stats['total_items'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 {{ $stats['alerts'] > 0 ? 'border-red-500' : 'border-green-500' }}">
            <p class="text-gray-500 text-sm font-bold uppercase">Low Stock Alerts</p>
            <p class="text-2xl font-black">{{ $stats['alerts'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-orange-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Expiring (30/60/90d)</p>
            <p class="text-2xl font-black">{{ $stats['expiring_30'] }} / {{ $stats['expiring_60'] }} / {{ $stats['expiring_90'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-emerald-500">
            <p class="text-gray-500 text-sm font-bold uppercase">Inventory Value</p>
            <p class="text-2xl font-black">${{ number_format($stats['inventory_value'], 2) }}</p>
        </div>
    </div>

    {{-- Monthly Stock Chart --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Monthly Stock Movement</h2>
        <p class="text-sm text-gray-500 mb-4">Last 12 months</p>
        <canvas id="stockChart" height="80"></canvas>
    </div>

    {{-- Expiry Forecast Tabs --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b flex gap-2">
            <button onclick="showExpiryTab('30')" id="tab-30" class="expiry-tab px-4 py-2 rounded font-bold bg-orange-500 text-white">
                30 Days ({{ $stats['expiring_30'] }})
            </button>
            <button onclick="showExpiryTab('60')" id="tab-60" class="expiry-tab px-4 py-2 rounded font-bold bg-gray-300 text-gray-700">
                60 Days ({{ $stats['expiring_60'] }})
            </button>
            <button onclick="showExpiryTab('90')" id="tab-90" class="expiry-tab px-4 py-2 rounded font-bold bg-gray-300 text-gray-700">
                90 Days ({{ $stats['expiring_90'] }})
            </button>
        </div>

        <div id="expiry-30" class="expiry-content p-4">
            @if($expiring30->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-left">Batch Code</th>
                            <th class="px-4 py-2 text-right">Quantity</th>
                            <th class="px-4 py-2 text-right">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiring30 as $b)
                        <tr class="border-b hover:bg-orange-50">
                            <td class="px-4 py-3 font-semibold">{{ $b->product->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $b->batch_code }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $b->current_quantity }}</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-bold">{{ $b->expiry_date->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-gray-500 py-8">No batches expiring in the next 30 days</p>
            @endif
        </div>

        <div id="expiry-60" class="expiry-content p-4 hidden">
            @if($expiring60->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-left">Batch Code</th>
                            <th class="px-4 py-2 text-right">Quantity</th>
                            <th class="px-4 py-2 text-right">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiring60 as $b)
                        <tr class="border-b hover:bg-yellow-50">
                            <td class="px-4 py-3 font-semibold">{{ $b->product->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $b->batch_code }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $b->current_quantity }}</td>
                            <td class="px-4 py-3 text-right text-yellow-600 font-bold">{{ $b->expiry_date->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-gray-500 py-8">No batches expiring in 31-60 days</p>
            @endif
        </div>

        <div id="expiry-90" class="expiry-content p-4 hidden">
            @if($expiring90->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-left">Batch Code</th>
                            <th class="px-4 py-2 text-right">Quantity</th>
                            <th class="px-4 py-2 text-right">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiring90 as $b)
                        <tr class="border-b hover:bg-blue-50">
                            <td class="px-4 py-3 font-semibold">{{ $b->product->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $b->batch_code }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ $b->current_quantity }}</td>
                            <td class="px-4 py-3 text-right text-blue-600 font-bold">{{ $b->expiry_date->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-gray-500 py-8">No batches expiring in 61-90 days</p>
            @endif
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b {{ $lowStock->count() > 0 ? 'bg-red-50 border-red-100' : 'bg-green-50 border-green-100' }}">
            <h2 class="font-bold {{ $lowStock->count() > 0 ? 'text-red-700' : 'text-green-700' }}">
                {{ $lowStock->count() > 0 ? 'Critical Low Stock' : 'Stock Levels Healthy' }}
            </h2>
        </div>
        @if($lowStock->count() > 0)
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-right">Current Stock</th>
                    <th class="px-4 py-2 text-right">Min Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStock as $p)
                <tr class="border-b hover:bg-red-50">
                    <td class="px-4 py-3 font-semibold">{{ $p->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->sku }}</td>
                    <td class="px-4 py-3 text-right text-red-600 font-bold">{{ $p->batches->sum('current_quantity') }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $p->min_stock_level }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-gray-600 font-medium">All products are above minimum stock levels</p>
            <p class="text-gray-500 text-sm mt-1">No restocking needed at this time</p>
        </div>
        @endif
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Stock Chart
const ctx = document.getElementById('stockChart').getContext('2d');
const stockChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyData['months']) !!},
        datasets: [{
            label: 'Stock In',
            data: {!! json_encode($monthlyData['stock_in']) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.3,
            fill: true
        }, {
            label: 'Stock Out',
            data: {!! json_encode($monthlyData['stock_out']) !!},
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Expiry Tab Switching
function showExpiryTab(days) {
    // Hide all content
    document.querySelectorAll('.expiry-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.expiry-tab').forEach(el => {
        el.classList.remove('bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'text-white');
        el.classList.add('bg-gray-300', 'text-gray-700');
    });

    // Show selected content
    document.getElementById('expiry-' + days).classList.remove('hidden');
    const tab = document.getElementById('tab-' + days);
    tab.classList.remove('bg-gray-300', 'text-gray-700');
    
    if (days === '30') {
        tab.classList.add('bg-orange-500', 'text-white');
    } else if (days === '60') {
        tab.classList.add('bg-yellow-500', 'text-white');
    } else {
        tab.classList.add('bg-blue-500', 'text-white');
    }
}
</script>
@endsection
