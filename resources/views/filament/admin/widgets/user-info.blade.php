@php
    $info = $this->getUserInfo();
@endphp

<div class="mb-4">
    <!-- Header Card -->
    <div class="rounded-xl overflow-hidden shadow-md mb-3 bg-gradient-to-br from-blue-700 to-indigo-800 dark:from-slate-800 dark:to-slate-900 border border-transparent dark:border-slate-700">
        <div class="p-6 text-white text-center">
            <svg class="w-10 h-10 mx-auto mb-2 text-white/90 dark:text-sky-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
            <h4 class="font-bold text-xl mb-1 text-white">Current Admin</h4>
            <p class="text-white/80 dark:text-slate-300 text-sm">Super Admin User</p>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="p-6">
            <!-- Name Section -->
            <div class="border-b border-gray-200 dark:border-slate-700 pb-4 mb-4">
                <h5 class="font-bold text-lg mb-1 text-gray-900 dark:text-slate-100">{{ $info['name'] }}</h5>
                <small class="text-gray-500 dark:text-slate-400">Admin Name</small>
            </div>

            <!-- Role Badge -->
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 rounded-full p-2 ml-3">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                </div>
                <div>
                    <small class="text-gray-500 dark:text-slate-400 block mb-1 font-semibold">ROLE</small>
                    <span class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">{{ ucfirst($info['role']) }}</span>
                </div>
            </div>

            <!-- Email Section -->
            <div class="flex items-start mb-4">
                <div class="bg-green-100 dark:bg-emerald-900/40 text-green-600 dark:text-emerald-300 rounded-full p-2 ml-3 flex-shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                </div>
                <div class="flex-grow">
                    <small class="text-gray-500 dark:text-slate-400 block mb-1 font-semibold">LOGIN EMAIL</small>
                    <p class="text-sm break-all text-gray-900 dark:text-slate-200">{{ $info['email'] }}</p>
                </div>
            </div>

            <!-- Super Admin Badge -->
            @if($info['is_super_admin'])
                <div class="bg-yellow-50 dark:bg-amber-950/40 border border-yellow-200 dark:border-amber-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-amber-400 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <strong class="text-yellow-800 dark:text-amber-300">Super Admin Access</strong>
                    </div>
                </div>
            @endif

            <!-- Organization Info -->
            <div class="bg-blue-50 dark:bg-slate-900/60 rounded-lg p-4 border border-blue-200 dark:border-slate-700">
                <small class="text-blue-700 dark:text-sky-400 block mb-1 font-semibold">ORGANIZATION</small>
                <p class="font-bold text-blue-900 dark:text-slate-100">{{ $info['tenant_name'] }}</p>
            </div>
        </div>
    </div>
</div>
