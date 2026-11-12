<?php

namespace App\Features\Clients\Resources;

use App\Features\Companies\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientDetailsResource extends JsonResource
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
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'last_follow_up_date' => $this->last_follow_up_date?->format('Y-m-d'),
            'companies' => $this->whenLoaded('companies', function () {
                return $this->companies->map(function (Company $company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                    ];
                })->all();
            }),
        ];
    }
}

