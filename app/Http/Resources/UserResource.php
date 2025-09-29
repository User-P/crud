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
            'role' => $this->role,
            'country_id' => $this->country_id,
            'email_verified_at' => $this->when($this->email_verified_at, function () {
                return $this->email_verified_at->toISOString();
            }),
            'is_admin' => $this->isAdmin(),
            'profile_complete' => $this->email_verified_at !== null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
