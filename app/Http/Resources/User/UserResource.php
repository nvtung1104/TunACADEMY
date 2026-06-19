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
            'frame'         => $this->frame,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'address'       => $this->address,
            'qualification'  => $this->qualification,
            'parent_name'    => $this->parent_name,
            'parent_phone'   => $this->parent_phone,
            'parent_email'   => $this->parent_email,
            'parent_address' => $this->parent_address,
            'status'         => $this->status,
            'roles'          => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
        ];
    }
}
