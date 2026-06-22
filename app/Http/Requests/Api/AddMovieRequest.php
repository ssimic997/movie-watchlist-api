<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddMovieRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'external_id' => ['nullable', 'string'],
            'title'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('external_id') && ! $this->filled('title')) {
                $validator->errors()->add('external_id', 'Provide either external_id or title.');
            }
        });
    }

}
