<div class="space-y-4" dir="rtl">
    @php
        $courses = $this->getCoursesData();
    @endphp

    @if(!empty($courses))
        @foreach($courses as $course)
            {{-- Modern Course Card --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                
                {{-- Elegant Header with Gradient --}}
                <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 px-6 py-5 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-4xl">📚</span>
                                <h3 class="text-2xl font-black text-white drop-shadow-lg">
                                    {{ $course['course_name'] }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="bg-white/25 backdrop-blur-md px-3 py-1 rounded-full text-white text-sm font-bold">
                                    {{ $course['course_code'] }}
                                </span>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-md px-6 py-3 rounded-2xl border-2 border-white/30 shadow-xl">
                            <div class="text-center">
                                <div class="text-sm text-white/90 font-semibold mb-1">السعر</div>
                                <div class="text-3xl font-black text-white">{{ number_format($course['course_price'], 0) }}</div>
                                <div class="text-xs text-white/80 font-bold">جنيه</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Premium Stats Grid --}}
                <div class="p-6 bg-gradient-to-br from-gray-50 to-white">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                        
                        {{-- Total Sessions Stat --}}
                        <div class="group relative bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl mb-3 group-hover:scale-110 transition-transform">📚</div>
                                <div class="text-4xl font-black text-white mb-2 drop-shadow-lg">
                                    {{ $course['total_sessions'] }}
                                </div>
                                <div class="text-sm font-bold text-blue-100 uppercase tracking-wider">إجمالي المحاضرات</div>
                            </div>
                            <div class="absolute -bottom-4 -right-4 text-8xl opacity-10">📚</div>
                        </div>

                        {{-- Completed Sessions Stat --}}
                        <div class="group relative bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl mb-3 group-hover:scale-110 transition-transform">✅</div>
                                <div class="text-4xl font-black text-white mb-2 drop-shadow-lg">
                                    {{ $course['completed_sessions'] }}
                                </div>
                                <div class="text-sm font-bold text-emerald-100 uppercase tracking-wider">تم شرحها</div>
                            </div>
                            <div class="absolute -bottom-4 -right-4 text-8xl opacity-10">✅</div>
                        </div>

                        {{-- Attendance Stat --}}
                        <div class="group relative bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl p-5 shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl mb-3 group-hover:scale-110 transition-transform">✓</div>
                                <div class="text-4xl font-black text-white mb-2 drop-shadow-lg">
                                    {{ $course['attended_sessions'] }}
                                </div>
                                <div class="text-sm font-bold text-purple-100 uppercase tracking-wider">المحاضرات المحضورة</div>
                            </div>
                            <div class="absolute -bottom-4 -right-4 text-8xl opacity-10">✓</div>
                        </div>
                    </div>

                    {{-- Enhanced Progress Section - TWO Progress Bars for Comparison --}}
                    @if($course['total_sessions'] > 0)
                        <div class="bg-gradient-to-br from-slate-50 to-gray-100 rounded-2xl p-6 border-2 border-slate-200 shadow-lg">
                            {{-- Header --}}
                            <div class="flex items-center gap-3 mb-6">
                                <div class="bg-gradient-to-br from-indigo-600 to-purple-600 p-3 rounded-xl shadow-lg">
                                    <span class="text-2xl">📊</span>
                                </div>
                                <div>
                                    <div class="text-xl font-black text-gray-800">مقارنة التقدم والحضور</div>
                                    <div class="text-xs text-gray-600 font-semibold">Progress & Attendance Comparison</div>
                                </div>
                            </div>

                            <div class="space-y-5">
                                {{-- Progress Bar 1: Session Completion --}}
                                <div class="relative">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">📚</span>
                                            <span class="text-sm font-bold text-gray-700">المحاضرات المكتملة</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-semibold text-gray-600">{{ $course['completed_sessions'] }} من {{ $course['total_sessions'] }}</span>
                                            <div class="bg-indigo-100 px-4 py-2 rounded-xl border-2 border-indigo-300">
                                                <span class="text-lg font-black text-indigo-600">{{ $course['session_progress'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Progress Bar with Scale --}}
                                    <div class="relative">
                                        <div class="w-full bg-gray-200 rounded-full h-8 overflow-hidden shadow-inner border-2 border-gray-300">
                                            <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-1000 ease-out relative flex items-center justify-end px-3" 
                                                 style="width: {{ $course['session_progress'] }}%">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-white/20"></div>
                                                @if($course['session_progress'] > 10)
                                                    <span class="relative z-10 text-white text-xs font-bold drop-shadow">{{ $course['completed_sessions'] }}/{{ $course['total_sessions'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Scale markers --}}
                                        <div class="flex justify-between mt-1 px-1">
                                            <span class="text-xs text-gray-400 font-semibold">0</span>
                                            <span class="text-xs text-gray-400 font-semibold">{{ $course['total_sessions'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Progress Bar 2: Attendance --}}
                                <div class="relative">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">✓</span>
                                            <span class="text-sm font-bold text-gray-700">المحاضرات المحضورة</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-semibold text-gray-600">{{ $course['attended_sessions'] }} من {{ $course['total_sessions'] }}</span>
                                            <div class="bg-emerald-100 px-4 py-2 rounded-xl border-2 border-emerald-300">
                                                <span class="text-lg font-black text-emerald-600">{{ $course['attendance_progress'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Attendance Progress Bar with Scale --}}
                                    <div class="relative">
                                        <div class="w-full bg-gray-200 rounded-full h-8 overflow-hidden shadow-inner border-2 border-gray-300">
                                            <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 transition-all duration-1000 ease-out relative flex items-center justify-end px-3" 
                                                 style="width: {{ $course['attendance_progress'] }}%">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-white/20"></div>
                                                @if($course['attendance_progress'] >= 50)
                                                    <div class="absolute inset-0 animate-pulse bg-white/10"></div>
                                                @endif
                                                @if($course['attendance_progress'] > 10)
                                                    <span class="relative z-10 text-white text-xs font-bold drop-shadow">{{ $course['attended_sessions'] }}/{{ $course['total_sessions'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Scale markers --}}
                                        <div class="flex justify-between mt-1 px-1">
                                            <span class="text-xs text-gray-400 font-semibold">0</span>
                                            <span class="text-xs text-gray-400 font-semibold">{{ $course['total_sessions'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Comparison Stats --}}
                                <div class="grid grid-cols-3 gap-3 pt-4 border-t-2 border-dashed border-gray-300">
                                    <div class="text-center bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl py-3 px-2 border-2 border-indigo-200">
                                        <div class="text-2xl font-black text-indigo-600">{{ $course['completed_sessions'] }}</div>
                                        <div class="text-xs text-gray-600 font-bold">محاضرات مكتملة</div>
                                    </div>
                                    <div class="text-center bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl py-3 px-2 border-2 border-emerald-200">
                                        <div class="text-2xl font-black text-emerald-600">{{ $course['attended_sessions'] }}</div>
                                        <div class="text-xs text-gray-600 font-bold">محاضرات محضورة</div>
                                    </div>
                                    <div class="text-center bg-gradient-to-br from-red-50 to-orange-50 rounded-xl py-3 px-2 border-2 border-red-200">
                                        <div class="text-2xl font-black text-red-600">{{ $course['completed_sessions'] - $course['attended_sessions'] }}</div>
                                        <div class="text-xs text-gray-600 font-bold">محاضرات غائب</div>
                                    </div>
                                </div>

                                {{-- Visual Comparison Alert --}}
                                @php
                                    $difference = $course['session_progress'] - $course['attendance_progress'];
                                @endphp
                                @if(abs($difference) > 10)
                                    <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 flex items-center gap-3">
                                        <span class="text-3xl">⚠️</span>
                                        <div class="flex-1">
                                            <div class="text-sm font-bold text-amber-800">
                                                @if($difference > 0)
                                                    يوجد فارق {{ round($difference, 1) }}% بين المحاضرات المكتملة والحضور
                                                @else
                                                    ⚠️ خطأ: الحضور أعلى من المحاضرات المكتملة!
                                                @endif
                                            </div>
                                            <div class="text-xs text-amber-700 mt-1">
                                                الطالب غائب عن {{ $course['completed_sessions'] - $course['attended_sessions'] }} محاضرة من المحاضرات المكتملة
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Collapsible Sessions Table --}}
                @if(!empty($course['sessions']))
                    <details class="border-t-2 border-gray-200">
                        <summary class="px-6 py-4 bg-gradient-to-r from-gray-100 to-gray-50 cursor-pointer hover:from-gray-200 hover:to-gray-100 transition-all duration-300 font-black text-gray-800 flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl group-hover:scale-110 transition-transform">📅</span>
                                <span class="text-lg">جدول المحاضرات</span>
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-sm font-bold shadow-md">
                                    {{ count($course['sessions']) }} محاضرة
                                </span>
                            </div>
                            <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        
                        <div class="p-6 bg-gray-50">
                            <div class="overflow-x-auto rounded-xl border-2 border-gray-200 shadow-md">
                                <table class="w-full">
                                    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                                        <tr>
                                            <th class="px-5 py-4 text-right text-sm font-black text-white">📅 التاريخ</th>
                                            <th class="px-5 py-4 text-right text-sm font-black text-white">📆 اليوم</th>
                                            <th class="px-5 py-4 text-right text-sm font-black text-white">⏰ من</th>
                                            <th class="px-5 py-4 text-right text-sm font-black text-white">⏱️ إلى</th>
                                            <th class="px-5 py-4 text-center text-sm font-black text-white">📊 الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y-2 divide-gray-200">
                                        @foreach($course['sessions'] as $index => $session)
                                            <tr class="hover:bg-indigo-50 transition-colors duration-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                                <td class="px-5 py-4 text-sm font-bold text-gray-900">{{ $session['date'] }}</td>
                                                <td class="px-5 py-4 text-sm font-semibold text-gray-700">{{ $session['day'] }}</td>
                                                <td class="px-5 py-4 text-sm font-semibold text-gray-700">{{ $session['start_time'] }}</td>
                                                <td class="px-5 py-4 text-sm font-semibold text-gray-700">{{ $session['end_time'] }}</td>
                                                <td class="px-5 py-4 text-center">
                                                    @if($session['status'] == 'منتهية')
                                                        <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-black rounded-full bg-gradient-to-r from-green-400 to-emerald-500 text-white shadow-lg">
                                                            <span>✅</span>
                                                            <span>{{ $session['status'] }}</span>
                                                        </span>
                                                    @elseif($session['status'] == 'جارية')
                                                        <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-black rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 text-white shadow-lg animate-pulse">
                                                            <span>🔵</span>
                                                            <span>{{ $session['status'] }}</span>
                                                        </span>
                                                    @elseif($session['status'] == 'مجدولة')
                                                        <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-black rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 text-white shadow-lg">
                                                            <span>📅</span>
                                                            <span>{{ $session['status'] }}</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-black rounded-full bg-gradient-to-r from-red-400 to-pink-500 text-white shadow-lg">
                                                            <span>❌</span>
                                                            <span>{{ $session['status'] }}</span>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @else
                    <div class="px-6 py-5 bg-gray-100 border-t-2 border-gray-200 text-center">
                        <div class="text-6xl mb-3">📭</div>
                        <p class="text-lg font-bold text-gray-600">لا توجد محاضرات مجدولة</p>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="bg-gradient-to-br from-gray-100 to-gray-50 rounded-2xl p-12 text-center border-2 border-dashed border-gray-300 shadow-inner">
            <div class="text-8xl mb-5 animate-bounce">📚</div>
            <h3 class="text-2xl font-black text-gray-700 mb-2">اختر طالباً أولاً</h3>
            <p class="text-gray-600 font-semibold">سيتم عرض جميع الكورسات والمحاضرات المسجلة للطالب هنا</p>
        </div>
    @endif
</div>
