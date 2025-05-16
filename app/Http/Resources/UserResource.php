<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'total_hours' => $this->when(isset($this->total_hours), function () {
                return (float) $this->total_hours;
            }, function () {
                return (float) \App\Models\TimeEntry::where('user_id', $this->id)->sum('hours');
            }),
            'total_hours_in_range' => $this->when(isset($this->total_hours_in_range), function () {
                return (float) $this->total_hours_in_range;
            }),
            'total_hours_in_project' => $this->when(isset($this->total_hours_in_project), function () {
                return (float) $this->total_hours_in_project;
            }),
            'projects_count' => $this->when(true, function () {
                return $this->projects()->count();
            }),
        ];
    }
}
