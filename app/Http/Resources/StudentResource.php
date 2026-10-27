<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EnrollmentResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Student',
    title: 'Student',
    description: 'نموذج بيانات الطالب',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
        new OA\Property(property: 'last_name', type: 'string', example: 'محمد'),
        new OA\Property(property: 'full_name', type: 'string', example: 'أحمد محمد'),
        new OA\Property(property: 'phone', type: 'string', example: '0501234567'),
        new OA\Property(property: 'email', type: 'string', example: 'ahmad@example.com'),
        new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
        new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', example: '2000-01-01'),
        new OA\Property(property: 'national_id', type: 'string', example: '1234567890'),
        new OA\Property(property: 'address', type: 'string', example: 'الرياض'),
        new OA\Property(property: 'notes', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'gender_label' => $this->gender === 'male' ? 'ذكر' : 'أنثى',
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->date_of_birth?->age,
            'national_id' => $this->national_id,
            'address' => $this->address,
            'notes' => $this->notes,
            
            // Relationships (when loaded)
            'enrollments' => EnrollmentResource::collection($this->whenLoaded('enrollments')),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
