<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
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
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'enrollment_date' => $this->enrollment_date?->format('Y-m-d'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            
            // Financial
            'total_amount' => number_format((float) $this->total_amount, 2),
            'paid_amount' => number_format((float) $this->paid_amount, 2),
            'remaining_amount' => number_format((float) $this->remaining_amount, 2),
            'discount_amount' => number_format((float) $this->discount_amount, 2),
            
            // Relationships (when loaded)
            'groups' => $this->whenLoaded('groups', fn() => 
                $this->groups->map(fn($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'code' => $group->code,
                ])
            ),
            'courses' => $this->whenLoaded('courses', fn() => 
                $this->courses->map(fn($course) => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'code' => $course->code,
                ])
            ),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
    
    /**
     * Get status label in Arabic
     */
    protected function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'suspended' => 'موقوف',
            default => $this->status,
        };
    }
}
