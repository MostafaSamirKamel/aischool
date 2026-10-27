<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($exam->images as $index => $image)
        <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-white hover:shadow-xl transition-all duration-300">
            <div class="relative group bg-gray-50">
                <img 
                    src="{{ route('exam.image', ['exam' => $exam->id, 'index' => $index]) }}" 
                    alt="صورة {{ $index + 1 }}"
                    class="w-full h-auto cursor-pointer hover:scale-105 transition-transform duration-300"
                    onclick="openImageInTab('{{ route('exam.image', ['exam' => $exam->id, 'index' => $index]) }}')"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
                    <button 
                        onclick="openImageInTab('{{ route('exam.image', ['exam' => $exam->id, 'index' => $index]) }}')"
                        class="bg-white text-gray-900 px-6 py-3 rounded-lg text-sm font-bold shadow-lg hover:bg-gray-100 transform hover:-translate-y-1 transition-all duration-200">
                        🔍 عرض بحجم كامل
                    </button>
                </div>
            </div>
            <div class="p-4 bg-gradient-to-r from-primary-50 to-primary-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="bg-primary-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">{{ $index + 1 }}</span>
                    <span class="text-sm text-gray-700 font-semibold">صورة {{ $index + 1 }}</span>
                </div>
                <a 
                    href="{{ route('exam.image', ['exam' => $exam->id, 'index' => $index]) }}" 
                    target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    فتح
                </a>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="flex items-start gap-3 text-sm bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5 shadow-sm">
        <div class="bg-blue-500 text-white rounded-full p-2 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-bold text-blue-900 mb-2">معلومات مهمة:</p>
            <ul class="space-y-1.5 text-blue-700">
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">✓</span>
                    <span>سيتمكن الطلاب من عرض هذه الصور بوضوح عالي أثناء حل الامتحان</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">✓</span>
                    <span>اضغط على أي صورة لعرضها بحجم أكبر في نافذة جديدة</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-green-600 font-bold">✓</span>
                    <span>يمكن للطلاب تحميل الصور والتكبير/التصغير حسب الحاجة</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    function openImageInTab(url) {
        window.open(url, '_blank');
    }
</script>
