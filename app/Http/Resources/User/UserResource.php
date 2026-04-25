<?php
namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'avatar'        => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'gender'        => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'qualification' => $this->qualification,
            'status'        => $this->status,
            'roles'         => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
        ];
    }
}
