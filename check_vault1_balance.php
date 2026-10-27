<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Force tenant context for vault id=1 (tenant_id=1)
$v = App\Models\Vault::withoutGlobalScopes()->find(1);

echo "=== خزنة: {$v->name} (ID={$v->id}) ===\n\n";

// Full cumulative (as getCurrentBalance shows in vault list)
$revenues        = (float) $v->revenues()->withoutGlobalScopes()->sum('amount');
$studentPayments = (float) $v->studentPayments()->withoutGlobalScopes()->where('status','completed')->sum('net_amount');
$enrollments     = (float) $v->enrollments()->withoutGlobalScopes()->sum('paid_amount');
$payments        = (float) $v->payments()->withoutGlobalScopes()->where('status','completed')->sum('amount');
$expenses        = (float) $v->expenses()->withoutGlobalScopes()->sum('amount');
$salary          = (float) $v->salaryPayments()->withoutGlobalScopes()->where('status','paid')->sum('amount');
$tin             = (float) $v->transfersTo()->withoutGlobalScopes()->where('status','completed')->sum('amount');
$tout            = (float) $v->transfersFrom()->withoutGlobalScopes()->where('status','completed')->sum('amount');
$opening         = (float) $v->opening_balance;

$total = $opening + $revenues + $studentPayments + $enrollments + $payments + $tin - $expenses - $salary - $tout;

echo "--- الرصيد الإجمالي التراكمي (ما يظهر في جدول الخزائن) ---\n";
echo " الرصيد الافتتاحي    = " . number_format($opening, 2) . "\n";
echo " إيرادات (revenues)  = " . number_format($revenues, 2) . "\n";
echo " student_payments    = " . number_format($studentPayments, 2) . "\n";
echo " enrollments.paid    = " . number_format($enrollments, 2) . "\n";
echo " payments            = " . number_format($payments, 2) . "\n";
echo " تحويلات واردة       = " . number_format($tin, 2) . "\n";
echo " مصروفات             = " . number_format($expenses, 2) . "\n";
echo " مرتبات مدفوعة       = " . number_format($salary, 2) . "\n";
echo " تحويلات صادرة       = " . number_format($tout, 2) . "\n";
echo "=> الرصيد الحالي الكلي = " . number_format($total, 2) . "\n\n";

// إيرادات إجمالية (المصدر المعروض في التقرير: revenues فقط أم كل الداخل؟)
$totalInflow  = $revenues + $studentPayments + $enrollments + $payments + $tin;
$totalOutflow = $expenses + $salary + $tout;
echo " إجمالي الداخل  = " . number_format($totalInflow, 2) . "\n";
echo " إجمالي الخارج = " . number_format($totalOutflow, 2) . "\n\n";

// Today's date context: May 1, 2026
$today    = '2026-05-01';
$todayEnd = '2026-05-01 23:59:59';

// Opening balance as of end of April 30 (cumulative until yesterday)
$revBefore     = (float) \Illuminate\Support\Facades\DB::table('revenues')->where('vault_id',1)->where(function($q){ $q->whereDate('date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');
$studBefore    = (float) \Illuminate\Support\Facades\DB::table('student_payments')->where('vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('payment_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('net_amount');
$enrBefore     = (float) \Illuminate\Support\Facades\DB::table('enrollments')->where('vault_id',1)->where(function($q){ $q->whereDate('enrollment_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('enrollment_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('paid_amount');
$payBefore     = (float) \Illuminate\Support\Facades\DB::table('payments')->where('vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('payment_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');
$expBefore     = (float) \Illuminate\Support\Facades\DB::table('expenses')->where('vault_id',1)->where(function($q){ $q->whereDate('date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');
$salBefore     = (float) \Illuminate\Support\Facades\DB::table('salary_payments')->where('vault_id',1)->where('status','paid')->where(function($q){ $q->whereDate('payment_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');
$tinBefore     = (float) \Illuminate\Support\Facades\DB::table('vault_transfers')->where('to_vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('transfer_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('transfer_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');
$toutBefore    = (float) \Illuminate\Support\Facades\DB::table('vault_transfers')->where('from_vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('transfer_date','<','2026-05-01')->orWhere(function($q2){ $q2->whereNull('transfer_date')->whereDate('created_at','<','2026-05-01'); }); })->sum('amount');

$openingToday = $opening + $revBefore + $studBefore + $enrBefore + $payBefore + $tinBefore - $expBefore - $salBefore - $toutBefore;

echo "--- رصيد بداية اليوم (نهاية أمس 30 أبريل) ---\n";
echo " rev قبل اليوم      = " . number_format($revBefore, 2) . "\n";
echo " stud قبل اليوم     = " . number_format($studBefore, 2) . "\n";
echo " enr قبل اليوم      = " . number_format($enrBefore, 2) . "\n";
echo " pay قبل اليوم      = " . number_format($payBefore, 2) . "\n";
echo " tin قبل اليوم      = " . number_format($tinBefore, 2) . "\n";
echo " exp قبل اليوم      = " . number_format($expBefore, 2) . "\n";
echo " sal قبل اليوم      = " . number_format($salBefore, 2) . "\n";
echo " tout قبل اليوم     = " . number_format($toutBefore, 2) . "\n";
echo "=> رصيد بداية اليوم = " . number_format($openingToday, 2) . "\n\n";

// Today's movements
$revToday  = (float) \Illuminate\Support\Facades\DB::table('revenues')->where('vault_id',1)->where(function($q){ $q->whereDate('date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');
$studToday = (float) \Illuminate\Support\Facades\DB::table('student_payments')->where('vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('payment_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','2026-05-01'); }); })->sum('net_amount');
$enrToday  = (float) \Illuminate\Support\Facades\DB::table('enrollments')->where('vault_id',1)->where(function($q){ $q->whereDate('enrollment_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('enrollment_date')->whereDate('created_at','2026-05-01'); }); })->sum('paid_amount');
$payToday  = (float) \Illuminate\Support\Facades\DB::table('payments')->where('vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('payment_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');
$expToday  = (float) \Illuminate\Support\Facades\DB::table('expenses')->where('vault_id',1)->where(function($q){ $q->whereDate('date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');
$salToday  = (float) \Illuminate\Support\Facades\DB::table('salary_payments')->where('vault_id',1)->where('status','paid')->where(function($q){ $q->whereDate('payment_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('payment_date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');
$tinToday  = (float) \Illuminate\Support\Facades\DB::table('vault_transfers')->where('to_vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('transfer_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('transfer_date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');
$toutToday = (float) \Illuminate\Support\Facades\DB::table('vault_transfers')->where('from_vault_id',1)->where('status','completed')->where(function($q){ $q->whereDate('transfer_date','2026-05-01')->orWhere(function($q2){ $q2->whereNull('transfer_date')->whereDate('created_at','2026-05-01'); }); })->sum('amount');

$inflowToday  = $revToday + $studToday + $enrToday + $payToday + $tinToday;
$outflowToday = $expToday + $salToday + $toutToday;
$closingToday = $openingToday + $inflowToday - $outflowToday;

echo "--- حركات اليوم (1 مايو 2026) ---\n";
echo " إيرادات اليوم         = " . number_format($revToday, 2) . "\n";
echo " student_pay اليوم     = " . number_format($studToday, 2) . "\n";
echo " تسجيلات اليوم         = " . number_format($enrToday, 2) . "\n";
echo " payments اليوم        = " . number_format($payToday, 2) . "\n";
echo " تحويلات واردة اليوم   = " . number_format($tinToday, 2) . "\n";
echo " مصروفات اليوم         = " . number_format($expToday, 2) . "\n";
echo " مرتبات اليوم          = " . number_format($salToday, 2) . "\n";
echo " تحويلات صادرة اليوم   = " . number_format($toutToday, 2) . "\n";
echo " إجمالي داخل اليوم     = " . number_format($inflowToday, 2) . "\n";
echo " إجمالي خارج اليوم     = " . number_format($outflowToday, 2) . "\n";
echo "=> رصيد ختامي اليوم    = " . number_format($closingToday, 2) . "\n\n";

echo "--- المقارنة مع ما قاله المستخدم ---\n";
echo " المستخدم يقول: إيرادات=4,000 | مصروفات=46,860 | رصيد ختامي=334,139\n";
echo " قاعدة البيانات تقول: داخل=" . number_format($inflowToday,2) . " | خارج=" . number_format($outflowToday,2) . " | ختامي=" . number_format($closingToday,2) . "\n";
echo " الفرق في الختامي = " . number_format($closingToday - 334139, 2) . "\n";
echo " رصيد جدول الخزائن = " . number_format($total, 2) . "\n";
