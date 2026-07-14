@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Customer-wise Sales</h1>

        <form method="GET" class="flex flex-wrap gap-4 items-end mb-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date:</label>
                <input type="date" name="start_date" value="{{ $start }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date:</label>
                <input type="date" name="end_date" value="{{ $end }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name:</label>
                <input type="text" name="customer_name" value="{{ $customer_name ?? '' }}"
                       placeholder="Search by name"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="self-end">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Filter
                </button>
            </div>
        </form>


        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Order Date</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customerSales as $customer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $customer->customer_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $customer->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 view-orders-btn"
                                data-customer-id="{{ $customer->customer_id }}"
                                data-customer-name="{{ $customer->customer_name }}">
                                View Orders
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Rs{{ number_format($customer->total_spent, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($customer->last_order_date)->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No sales found for this period.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders Modal -->
    <div id="ordersModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg w-11/12 max-w-2xl p-6 relative">
            <h2 class="text-xl font-bold mb-4" id="modalCustomerName">Orders</h2>
            <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">&times;</button>

            <div id="ordersContent" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Orders will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('ordersModal');
            const ordersContent = document.getElementById('ordersContent');
            const modalCustomerName = document.getElementById('modalCustomerName');
            const closeModal = document.getElementById('closeModal');

            document.querySelectorAll('.view-orders-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.dataset.customerId;
                    const customerName = this.dataset.customerName;

                    modalCustomerName.textContent = `Orders of ${customerName}`;
                    ordersContent.innerHTML = 'Loading...';

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // Fetch orders via AJAX
                    fetch(`/admin/reports/customer-orders/${customerId}`)
                        .then(res => res.json())
                        .then(data => {
                            const orders = data.orders;

                            if (orders.length === 0) {
                                ordersContent.innerHTML = '<p>No orders found.</p>';
                                return;
                            }

                            let html = '<ul class="divide-y divide-gray-200">';
                            orders.forEach(order => {
                                html += `<li class="py-2 flex justify-between items-center">
                            <span>Order #${order.order_number} - Rs${order.total} (${order.date})</span>
                            <a href="/admin/orders/${order.id}" class="text-blue-600 hover:underline">View Details</a>

                        </li>`;
                            });
                            html += '</ul>';

                            ordersContent.innerHTML = html;
                        })
                        .catch(err => {
                            ordersContent.innerHTML = '<p class="text-red-500">Error loading orders.</p>';
                            console.error(err);
                        });
                });
            });

            closeModal.addEventListener('click', function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
@endsection
