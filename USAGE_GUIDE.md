# 💡 دليل الاستخدام العملي - Quick Reference

> دليل سريع لاستخدام التحسينات الجديدة في المشروع

---

## 🎯 Enums - أمثلة عملية

### استخدام في الـ Models:

```php
use App\Enums\UserStatus;
use App\Enums\Gender;

class User extends Model
{
    protected $casts = [
        'status' => UserStatus::class,
        'gender' => Gender::class,
    ];
}

// Usage
$user = User::find(1);
echo $user->status->label(); // "نشط"
echo $user->status->color(); // "success"
```

### استخدام في Filament Forms:

```php
use App\Enums\UserStatus;
use App\Enums\PaymentMethod;

Select::make('status')
    ->label('الحالة')
    ->options(UserStatus::options())
    ->default(UserStatus::ACTIVE->value)
    ->native(false),

Select::make('payment_method')
    ->label('طريقة الدفع')
    ->options(PaymentMethod::options())
    ->required(),
```

### استخدام في Validation:

```php
use App\Enums\StudentStatus;
use Illuminate\Validation\Rule;

public function rules(): array
{
    return [
        'status' => ['required', Rule::enum(StudentStatus::class)],
    ];
}
```

### استخدام في Queries:

```php
use App\Enums\GroupStatus;

$activeGroups = Group::where('status', GroupStatus::ACTIVE->value)->get();

// Or with Eloquent cast
$activeGroups = Group::where('status', GroupStatus::ACTIVE)->get();
```

---

## 📐 Constants - أمثلة عملية

### استخدام في File Uploads:

```php
use App\Constants\FileConstants;

FileUpload::make('avatar')
    ->image()
    ->maxSize(FileConstants::MAX_IMAGE_SIZE)
    ->directory(FileConstants::STUDENT_IMAGES_DIR)
    ->acceptedFileTypes(FileConstants::ALLOWED_IMAGE_TYPES),
```

### استخدام في Validation:

```php
use App\Constants\SystemConstants;

TextInput::make('name')
    ->maxLength(SystemConstants::MAX_NAME_LENGTH)
    ->required(),

TextInput::make('national_id')
    ->length(SystemConstants::NATIONAL_ID_LENGTH)
    ->numeric(),

TextInput::make('description')
    ->maxLength(SystemConstants::MAX_DESCRIPTION_LENGTH)
    ->textarea(),
```

### استخدام في Password Validation:

```php
use App\Constants\SystemConstants;
use Illuminate\Validation\Rules\Password;

Password::min(SystemConstants::MIN_PASSWORD_LENGTH)
    ->letters()
    ->numbers()
    ->mixedCase()
    ->symbols()
```

### استخدام في Pagination:

```php
use App\Constants\SystemConstants;

Table::make()
    ->paginated(SystemConstants::PAGINATION_OPTIONS)
    ->defaultPaginationPageOption(SystemConstants::DEFAULT_PER_PAGE),
```

---

## 🏗️ Repository Pattern - أمثلة عملية

### إنشاء Repository جديد:

```php
namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function getActiveCourses()
    {
        return $this->model
            ->where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();
    }

    public function findByCode(string $code)
    {
        return $this->findOneBy('code', $code);
    }
}
```

### استخدام في Controller:

```php
namespace App\Http\Controllers;

use App\Repositories\StudentRepository;
use App\Repositories\CourseRepository;

class EnrollmentController extends Controller
{
    public function __construct(
        protected StudentRepository $students,
        protected CourseRepository $courses
    ) {}

    public function create()
    {
        return view('enrollments.create', [
            'students' => $this->students->getActiveStudents(),
            'courses' => $this->courses->getActiveCourses(),
        ]);
    }

    public function store(Request $request)
    {
        $student = $this->students->find($request->student_id);
        $course = $this->courses->find($request->course_id);

        // Create enrollment
    }
}
```

### استخدام في Service:

```php
namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\EnrollmentRepository;

class EnrollmentService
{
    public function __construct(
        protected StudentRepository $students,
        protected EnrollmentRepository $enrollments
    ) {}

    public function enrollStudent(int $studentId, int $groupId): bool
    {
        $student = $this->students->findOrFail($studentId);

        if ($this->enrollments->isStudentEnrolled($studentId, $groupId)) {
            return false;
        }

        $this->enrollments->enrollStudent($studentId, $groupId);
        return true;
    }
}
```

---

## 📝 Form Requests - أمثلة عملية

### إنشاء Form Request جديد:

```php
namespace App\Http\Requests;

use App\Constants\SystemConstants;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Create:Course');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:' . SystemConstants::MAX_NAME_LENGTH],
            'code' => ['required', 'string', 'unique:courses,code'],
            'description' => ['nullable', 'string', 'max:' . SystemConstants::MAX_DESCRIPTION_LENGTH],
            'department_id' => ['required', 'exists:departments,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم الكورس',
            'code' => 'كود الكورس',
            'description' => 'الوصف',
            'department_id' => 'القسم',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'هذا الكود مستخدم بالفعل',
        ];
    }
}
```

### استخدام في Controller:

```php
public function store(StoreCourseRequest $request)
{
    // Already validated!
    $course = Course::create($request->validated());

    return redirect()->route('courses.show', $course);
}

public function update(UpdateCourseRequest $request, Course $course)
{
    $course->update($request->validated());

    return redirect()->route('courses.show', $course);
}
```

---

## 📁 File Upload Service - أمثلة عملية

### استخدام في Controller:

```php
use App\Services\FileUploadService;
use App\Constants\FileConstants;

class ProfileController extends Controller
{
    public function updateAvatar(Request $request, FileUploadService $uploader)
    {
        $request->validate([
            'avatar' => 'required|image|max:' . FileConstants::MAX_IMAGE_SIZE,
        ]);

        // Delete old avatar
        if ($request->user()->avatar) {
            $uploader->delete($request->user()->avatar);
        }

        // Upload new avatar
        $path = $uploader->uploadImage(
            $request->file('avatar'),
            FileConstants::STUDENT_IMAGES_DIR
        );

        $request->user()->update(['avatar' => $path]);

        return back()->with('success', 'تم تحديث الصورة بنجاح');
    }
}
```

### استخدام في Livewire Component:

```php
use App\Services\FileUploadService;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadDocument extends Component
{
    use WithFileUploads;

    public $document;

    public function save(FileUploadService $uploader)
    {
        $this->validate([
            'document' => 'required|file|max:10240',
        ]);

        $path = $uploader->uploadDocument(
            $this->document,
            'documents'
        );

        Document::create([
            'path' => $path,
            'name' => $this->document->getClientOriginalName(),
        ]);

        $this->reset('document');
        session()->flash('message', 'تم رفع المستند بنجاح');
    }
}
```

---

## 🔐 Two-Factor Authentication - أمثلة عملية

### Enable 2FA Page:

```php
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function enable(TwoFactorAuthService $twoFactor)
    {
        $user = auth()->user();

        // Generate secret
        $secret = $twoFactor->generateSecret();
        $user->update(['two_factor_secret' => $secret]);

        // Get QR code
        $qrCodeUrl = $twoFactor->getQRCodeUrl($user, $secret);

        return view('profile.2fa-enable', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    public function confirm(Request $request, TwoFactorAuthService $twoFactor)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($twoFactor->enableFor($user, $request->code)) {
            $recoveryCodes = $twoFactor->getRecoveryCodes($user);

            return view('profile.2fa-recovery-codes', [
                'recoveryCodes' => $recoveryCodes,
            ]);
        }

        return back()->withErrors(['code' => 'الكود غير صحيح']);
    }
}
```

### Login with 2FA:

```php
use App\Services\TwoFactorAuthService;

class LoginController extends Controller
{
    public function login(Request $request, TwoFactorAuthService $twoFactor)
    {
        // Validate credentials
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'البيانات غير صحيحة']);
        }

        $user = Auth::user();

        // Check if 2FA is enabled
        if ($twoFactor->isEnabled($user)) {
            Auth::logout();
            session(['2fa_user_id' => $user->id]);

            return redirect()->route('2fa.verify');
        }

        return redirect()->intended('/dashboard');
    }

    public function verify2FA(Request $request, TwoFactorAuthService $twoFactor)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        $user = User::findOrFail($userId);

        if ($twoFactor->verify($user, $request->code)) {
            Auth::login($user);
            session()->forget('2fa_user_id');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['code' => 'الكود غير صحيح']);
    }
}
```

---

## 🔒 Data Encryption - أمثلة عملية

### استخدام في Model Accessors/Mutators:

```php
use App\Services\DataEncryptionService;

class Student extends Model
{
    // Encrypt on save
    public function setNationalIdAttribute($value)
    {
        $encryption = app(DataEncryptionService::class);
        $this->attributes['national_id'] = $encryption->encryptNationalId($value);
    }

    // Decrypt on read
    public function getNationalIdAttribute($value)
    {
        if (!$value) return null;

        $encryption = app(DataEncryptionService::class);
        return $encryption->decryptNationalId($value);
    }

    // Masked version for display
    public function getMaskedNationalIdAttribute()
    {
        if (!$this->national_id) return null;

        $encryption = app(DataEncryptionService::class);
        return $encryption->maskNationalId($this->national_id);
    }

    // Encrypted phone
    public function setPhoneAttribute($value)
    {
        $encryption = app(DataEncryptionService::class);
        $this->attributes['phone'] = $encryption->encryptPhone($value);
    }

    public function getPhoneAttribute($value)
    {
        if (!$value) return null;

        $encryption = app(DataEncryptionService::class);
        return $encryption->decryptPhone($value);
    }

    public function getMaskedPhoneAttribute()
    {
        if (!$this->phone) return null;

        $encryption = app(DataEncryptionService::class);
        return $encryption->maskPhone($this->phone);
    }
}
```

### عرض البيانات الحساسة:

```blade
{{-- Show masked data by default --}}
<p>الهاتف: {{ $student->masked_phone }}</p>
<p>الرقم القومي: {{ $student->masked_national_id }}</p>

{{-- Show real data only with permission --}}
@can('view-sensitive-data')
    <p>الهاتف الكامل: {{ $student->phone }}</p>
    <p>الرقم القومي الكامل: {{ $student->national_id }}</p>
@endcan
```

---

## 🧪 Testing - أمثلة عملية

### كتابة Unit Test:

```php
namespace Tests\Unit;

use App\Models\Course;
use App\Repositories\CourseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CourseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CourseRepository(new Course());
    }

    /** @test */
    public function it_can_get_active_courses(): void
    {
        // Arrange
        Course::factory()->count(3)->create(['is_active' => true]);
        Course::factory()->count(2)->create(['is_active' => false]);

        // Act
        $activeCourses = $this->repository->getActiveCourses();

        // Assert
        $this->assertCount(3, $activeCourses);
    }

    /** @test */
    public function it_can_find_course_by_code(): void
    {
        // Arrange
        $course = Course::factory()->create(['code' => 'CS101']);

        // Act
        $found = $this->repository->findByCode('CS101');

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('CS101', $found->code);
    }
}
```

### كتابة Feature Test:

```php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorized_user_can_create_student(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo('Create:Student');

        // Act
        $response = $this->actingAs($user)->post('/students', [
            'name' => 'أحمد محمد',
            'email' => 'ahmad@example.com',
            'student_code' => 'STU001',
            'phone' => '01234567890',
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'student_code' => 'STU001',
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_create_student(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/students', [
            'name' => 'أحمد محمد',
        ]);

        $response->assertForbidden();
    }
}
```

---

## 🎨 Filament Integration Examples

### Resource with Enums and Constants:

```php
use App\Enums\StudentStatus;
use App\Enums\Gender;
use App\Constants\FileConstants;
use App\Constants\SystemConstants;

class StudentResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(SystemConstants::MAX_NAME_LENGTH),

                        Select::make('gender')
                            ->options(Gender::options())
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->options(StudentStatus::options())
                            ->default(StudentStatus::ACTIVE->value)
                            ->required(),

                        FileUpload::make('avatar')
                            ->image()
                            ->maxSize(FileConstants::MAX_IMAGE_SIZE)
                            ->directory(FileConstants::STUDENT_IMAGES_DIR),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (StudentStatus $state) => $state->color())
                    ->formatStateUsing(fn (StudentStatus $state) => $state->label()),

                TextColumn::make('gender')
                    ->formatStateUsing(fn (Gender $state) => $state->label()),
            ])
            ->paginated(SystemConstants::PAGINATION_OPTIONS);
    }
}
```

---

## 📱 SMS Service - أمثلة عملية

### إرسال رسالة بسيطة:

```php
use App\Services\SmsService;

$sms = app(SmsService::class);

// Send simple message
$sms->send('+201234567890', 'مرحباً بك في النظام');
```

### إرسال template:

```php
// في config/sms.php
'templates' => [
    'welcome' => 'مرحباً {name} في {school_name}',
    'payment_received' => 'تم استلام دفعة بقيمة {amount} جنيه',
],

// Usage
$sms->sendTemplate('+201234567890', 'welcome', [
    'name' => 'أحمد',
]);

$sms->sendTemplate('+201234567890', 'payment_received', [
    'amount' => '500',
]);
```

### تغيير Provider في وقت التشغيل:

```php
$sms = app(SmsService::class);

// Use Twilio
$sms->setProvider('twilio')->send($phone, $message);

// Use Vodafone
$sms->setProvider('vodafone')->send($phone, $message);

// Use Log (for testing)
$sms->setProvider('log')->send($phone, $message);
```

---

## 🎯 Best Practices Summary

### ✅ DO:
- استخدم Enums للقيم المحددة
- استخدم Constants للأرقام الثابتة
- استخدم Repositories للـ data access
- استخدم Form Requests للـ validation
- اكتب Tests لكل feature جديد
- استخدم Type hints دائماً
- استخدم FileUploadService للملفات
- استخدم DataEncryptionService للبيانات الحساسة

### ❌ DON'T:
- لا تستخدم string literals للـ status
- لا تستخدم magic numbers
- لا تكتب SQL مباشرة في Controllers
- لا تضع validation في Controllers
- لا تنسى كتابة Tests
- لا تحفظ ملفات بدون validation
- لا تحفظ بيانات حساسة بدون encryption

---

**Happy Coding!** 🚀
