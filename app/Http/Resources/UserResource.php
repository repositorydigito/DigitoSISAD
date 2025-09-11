<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
                return (float) ($this->time_entries_sum_hours ?? $this->total_hours_in_range_sum_hours ?? \App\Models\TimeEntry::where('user_id', $this->id)->sum('hours'));
            }),
            'total_hours_in_range' => $this->when(isset($this->total_hours_in_range_sum_hours), function () {
                return (float) $this->total_hours_in_range_sum_hours;
            }),
            'total_hours_in_project' => (float) ($this->time_entries_sum_hours ?? 0),
            'projects_count' => $this->when(true, function () {
                return $this->projects()->count();
            }),
            'last_time_entry' => [
                'date' => $this->last_time_entry ? Carbon::parse($this->last_time_entry)->format('Y-m-d H:i:s') : null,
                'human_diff' => $this->last_time_entry ? Carbon::parse($this->last_time_entry)->diffForHumans() : null,
                'days_since_last_entry' => $this->last_time_entry ? Carbon::parse($this->last_time_entry)->diffInDays(now()) : null,
            ],
        ];
    }
}
