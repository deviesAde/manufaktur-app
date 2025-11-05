<div class="grid grid-cols-2 gap-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Stok Aman</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $safeStock }}</p>
            </div>
            <x-heroicon-o-check-circle class="w-8 h-8 text-green-500 dark:text-green-400" />
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Stok Rendah</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $lowStock }}</p>
            </div>
            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-500 dark:text-red-400" />
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $todaySales }}</p>
            </div>
            <x-heroicon-o-arrow-trending-up class="w-8 h-8 text-blue-500 dark:text-blue-400" />
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Produk</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $totalProducts }}</p>
            </div>
            <x-heroicon-o-cube class="w-8 h-8 text-purple-500 dark:text-purple-400" />
        </div>
    </div>
</div>
