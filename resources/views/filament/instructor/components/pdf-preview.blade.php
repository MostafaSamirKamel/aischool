@php
    $exam = $getRecord();
    $pdfUrl = $exam && $exam->pdf_file ? route('exam.pdf', ['filename' => basename($exam->pdf_file)]) : null;
@endphp

@if($pdfUrl)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-white bg-opacity-25 rounded p-2">
                    <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                </div>
                <span class="fw-semibold">معاينة ملف PDF</span>
            </div>
            <a href="{{ $pdfUrl }}" 
               target="_blank" 
               class="btn btn-light btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>فتح في نافذة جديدة</span>
            </a>
        </div>
        
        <div class="card-body p-4">
            <div class="border border-2 border-dashed rounded-3 p-5 text-center bg-light">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-file-earmark-pdf text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">ملف PDF جاهز للعرض</h5>
                <p class="text-muted mb-4">اضغط على الزر بالأسفل لفتح وعرض الملف</p>
                <a href="{{ $pdfUrl }}" 
                   target="_blank" 
                   class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2">
                    <i class="bi bi-eye"></i>
                    <span>عرض الملف</span>
                </a>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
@endif
