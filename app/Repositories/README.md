# 📚 Repository Pattern Documentation

## Overview

The Repository Pattern provides a clean separation between business logic and data access logic. It acts as an intermediary layer between the application and the database.

## Benefits

- ✅ **Separation of Concerns** - Business logic separated from data access
- ✅ **Testability** - Easy to mock repositories in tests
- ✅ **Reusability** - Common queries in one place
- ✅ **Maintainability** - Changes to data access don't affect business logic
- ✅ **SOLID Principles** - Follows Dependency Inversion Principle

## Available Repositories

### 1. StudentRepository
Location: `app/Repositories/StudentRepository.php`

Methods:
- `getActiveStudents()` - Get all active students
- `getByGroup(int $groupId)` - Get students in a group
- `getByCourse(int $courseId)` - Get students enrolled in a course
- `search(string $term)` - Search students by name, email, code, or national ID
- `findWithRelations(int $id)` - Get student with all relationships
- `getStatistics()` - Get student statistics

### 2. EnrollmentRepository
Location: `app/Repositories/EnrollmentRepository.php`

Methods:
- `getActiveEnrollments()` - Get all active enrollments
- `getByStudent(int $studentId)` - Get student's enrollments
- `getByGroup(int $groupId)` - Get group's enrollments
- `isStudentEnrolled(int $studentId, int $groupId)` - Check enrollment status
- `enrollStudent(int $studentId, int $groupId, array $data)` - Enroll student
- `completeEnrollment(int $id)` - Mark enrollment as completed
- `cancelEnrollment(int $id, ?string $reason)` - Cancel enrollment

## Creating a New Repository

### Step 1: Create Repository Class

```php
<?php

namespace App\Repositories;

use App\Models\YourModel;
use Illuminate\Database\Eloquent\Collection;

class YourModelRepository extends BaseRepository
{
    public function __construct(YourModel $model)
    {
        parent::__construct($model);
    }

    /**
     * Add your custom methods here
     */
    public function findByCustomField(string $value): ?YourModel
    {
        return $this->findOneBy('custom_field', $value);
    }

    public function getActiveRecords(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

### Step 2: Register in Service Provider (Optional)

If you need dependency injection:

```php
// app/Providers/AppServiceProvider.php

public function register(): void
{
    $this->app->bind(YourModelRepository::class);
}
```

### Step 3: Use in Controller

```php
<?php

namespace App\Http\Controllers;

use App\Repositories\YourModelRepository;

class YourController extends Controller
{
    public function __construct(
        protected YourModelRepository $repository
    ) {}

    public function index()
    {
        $records = $this->repository->getActiveRecords();
        return view('your-view', compact('records'));
    }

    public function store(Request $request)
    {
        $record = $this->repository->create($request->validated());
        return redirect()->route('your-route', $record);
    }
}
```

## Base Repository Methods

All repositories inherit these methods from `BaseRepository`:

### CRUD Operations

```php
// Get all records
$all = $repository->all();

// Find by ID
$record = $repository->find($id);

// Find or fail
$record = $repository->findOrFail($id);

// Create
$record = $repository->create([
    'field' => 'value',
]);

// Update
$repository->update($id, [
    'field' => 'new value',
]);

// Delete
$repository->delete($id);
```

### Queries

```php
// Paginate
$records = $repository->paginate(15);

// Find by field
$records = $repository->findBy('status', 'active');

// Find one by field
$record = $repository->findOneBy('email', 'user@example.com');

// Get query builder
$query = $repository->query()
    ->where('field', 'value')
    ->orderBy('created_at')
    ->get();
```

## Testing Repositories

### Unit Test Example

```php
<?php

namespace Tests\Unit\Repositories;

use App\Models\YourModel;
use App\Repositories\YourModelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YourModelRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected YourModelRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new YourModelRepository(new YourModel());
    }

    /** @test */
    public function it_can_create_record(): void
    {
        $record = $this->repository->create([
            'field' => 'value',
        ]);

        $this->assertInstanceOf(YourModel::class, $record);
        $this->assertEquals('value', $record->field);
    }

    /** @test */
    public function it_can_find_record(): void
    {
        $created = YourModel::factory()->create();

        $found = $this->repository->find($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
    }
}
```

### Mocking in Tests

```php
use App\Repositories\StudentRepository;
use Mockery;

/** @test */
public function it_uses_repository(): void
{
    // Create mock
    $mockRepo = Mockery::mock(StudentRepository::class);

    // Define expectations
    $mockRepo->shouldReceive('getActiveStudents')
        ->once()
        ->andReturn(collect([/* students */]));

    // Bind mock to container
    $this->app->instance(StudentRepository::class, $mockRepo);

    // Test your code
    $response = $this->get('/students');

    $response->assertOk();
}
```

## Best Practices

### ✅ DO:

1. **Keep repositories focused**
```php
// Good - Specific to Student
class StudentRepository extends BaseRepository
{
    public function getActiveStudents() { }
    public function getByGroup(int $groupId) { }
}
```

2. **Return eloquent types**
```php
// Good - Returns Collection or Model
public function getActiveStudents(): Collection
{
    return $this->model->where('status', 'active')->get();
}
```

3. **Use type hints**
```php
// Good
public function find(int $id): ?Student
{
    return $this->model->find($id);
}
```

4. **Name methods descriptively**
```php
// Good
public function getActiveStudentsOrderedByName(): Collection

// Bad
public function get(): Collection
```

### ❌ DON'T:

1. **Don't put business logic in repositories**
```php
// Bad - Business logic belongs in Service
public function enrollStudentInCourse($studentId, $courseId)
{
    // Complex enrollment logic with validations
    // This should be in EnrollmentService
}

// Good - Just data access
public function findByStudentAndCourse($studentId, $courseId)
{
    return $this->model
        ->where('student_id', $studentId)
        ->where('course_id', $courseId)
        ->first();
}
```

2. **Don't return different types**
```php
// Bad - Inconsistent return type
public function find($id)
{
    $student = $this->model->find($id);
    return $student ?? [];
}

// Good - Consistent return type
public function find(int $id): ?Student
{
    return $this->model->find($id);
}
```

3. **Don't access other repositories directly**
```php
// Bad
class StudentRepository
{
    public function getWithCourses($id)
    {
        $courseRepo = new CourseRepository();
        // Don't do this
    }
}

// Good - Use relationships
class StudentRepository
{
    public function findWithCourses(int $id): ?Student
    {
        return $this->model
            ->with('courses')
            ->find($id);
    }
}
```

## Advanced Patterns

### Repository with Service

```php
// Service
class EnrollmentService
{
    public function __construct(
        protected StudentRepository $students,
        protected EnrollmentRepository $enrollments,
        protected GroupRepository $groups
    ) {}

    public function enrollStudent(int $studentId, int $groupId): bool
    {
        // Business logic here
        $student = $this->students->findOrFail($studentId);
        $group = $this->groups->findOrFail($groupId);

        if ($this->enrollments->isStudentEnrolled($studentId, $groupId)) {
            throw new \Exception('Already enrolled');
        }

        $this->enrollments->enrollStudent($studentId, $groupId);

        // Send notifications, etc.

        return true;
    }
}
```

### Repository with Criteria

```php
class StudentRepository extends BaseRepository
{
    public function getByCriteria(array $criteria): Collection
    {
        $query = $this->query();

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['group_id'])) {
            $query->whereHas('enrollments', function ($q) use ($criteria) {
                $q->where('group_id', $criteria['group_id']);
            });
        }

        if (isset($criteria['search'])) {
            $query->whereHas('user', function ($q) use ($criteria) {
                $q->where('name', 'like', "%{$criteria['search']}%");
            });
        }

        return $query->get();
    }
}
```

## Migration Guide

### From Direct Eloquent to Repository

**Before:**
```php
class StudentController extends Controller
{
    public function index()
    {
        $students = Student::where('status', 'active')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('students.index', compact('students'));
    }

    public function search(Request $request)
    {
        $students = Student::whereHas('user', function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->term}%");
        })->get();

        return response()->json($students);
    }
}
```

**After:**
```php
class StudentController extends Controller
{
    public function __construct(
        protected StudentRepository $students
    ) {}

    public function index()
    {
        $students = $this->students->query()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('students.index', compact('students'));
    }

    public function search(Request $request)
    {
        $students = $this->students->search($request->term);

        return response()->json($students);
    }
}
```

## Conclusion

The Repository Pattern provides a clean, testable, and maintainable way to handle data access in your application. Follow the guidelines and best practices to get the most benefit from this pattern.
