@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Product Management</h1>

    {{-- Success/Error Notifications --}}
    @if(session('success'))
        <div id="successNotification" class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div id="errorNotification" class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-red-700 font-bold mb-1">Action Blocked</p>
                    @foreach($errors->all() as $error)
                        <p class="text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(session('cascade_update'))
        <div id="cascadeNotification" class="mb-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-blue-700 font-bold mb-1">Cascade Update Applied</p>
                    <p class="text-blue-600">
                        {{ ucfirst(session('cascade_update')['type']) }} name changed from 
                        <span class="font-semibold">"{{ session('cascade_update')['old_name'] }}"</span> to 
                        <span class="font-semibold">"{{ session('cascade_update')['new_name'] }}"</span>
                    </p>
                    <p class="text-blue-600 mt-1">
                        ✓ {{ session('cascade_update')['count'] }} product(s) automatically updated
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Horizontal Tab Navigation --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="flex border-b border-gray-200">
            <button onclick="switchTab('products')" id="tab-products" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                Products
            </button>
            <button onclick="switchTab('categories')" id="tab-categories" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Categories
            </button>
            <button onclick="switchTab('units')" id="tab-units" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Units
            </button>
            <button onclick="switchTab('suppliers')" id="tab-suppliers" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Suppliers
            </button>
        </div>
    </div>

    {{-- Tab Content Containers --}}
    <div id="content-products" class="tab-content">
        @include('products.partials.products-tab')
    </div>

    <div id="content-categories" class="tab-content hidden">
        @include('products.partials.categories-tab')
    </div>

    <div id="content-units" class="tab-content hidden">
        @include('products.partials.units-tab')
    </div>

    <div id="content-suppliers" class="tab-content hidden">
        @include('products.partials.suppliers-tab')
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

// Check URL hash on page load to show correct tab
window.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    if (hash && ['products', 'categories', 'units', 'suppliers'].includes(hash)) {
        switchTab(hash);
    }
    
    // Auto-dismiss notifications after 5 seconds
    setTimeout(function() {
        const notifications = ['successNotification', 'errorNotification', 'cascadeNotification'];
        notifications.forEach(function(id) {
            const element = document.getElementById(id);
            if (element) {
                element.style.transition = 'opacity 0.5s ease-out';
                element.style.opacity = '0';
                setTimeout(function() {
                    element.remove();
                }, 500);
            }
        });
    }, 5000);
});
</script>
@endsection
