<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertySaleHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return collect($this->resource)->map(function ($value) {
            if (is_array($value)) {
                return implode(', ', $value);
            }

            if (is_string($value) && str_contains($value, ',')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return implode(', ', $decoded);
                }
            }

            return $value;
        })->toArray();
    }
}
