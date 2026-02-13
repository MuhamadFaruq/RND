<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat Datang, {{ auth()->user()->name }}!</h3>
                    <p class="text-gray-600">Anda masuk sebagai: <span class="font-semibold text-indigo-600 uppercase">{{ auth()->user()->role }}</span></p>
                    
                    <hr class="my-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if(auth()->user()->role === 'marketing')
                            <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-bold text-blue-700">Marketing Menu</h4>
                                <a href="{{ route('marketing.orders.index') }}" class="mt-2 inline-block text-sm text-blue-600 underline">Lihat Order List</a>
                            </div>
                        @endif

                        @if(auth()->user()->role === 'admin')
                            <div class="p-6 bg-red-50 border border-red-200 rounded-lg">
                                <h4 class="font-bold text-red-700">Admin Control</h4>
                                <p class="text-sm">Manajemen User & Monitoring System</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>