<?php

namespace App\Http\Requests\Api;

use App\Enum\MovieStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWatchlistMovieRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status'      => ['sometimes', Rule::enum(MovieStatus::class)],
            'user_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10'],
            'notes'       => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
