<?php

namespace App\Http\Requests\Api;

use App\Enum\MovieStatus;
use App\Enum\WatchlistMovieSortBy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWatchlistMoviesRequest extends FormRequest
{
    private const DEFAULT_PER_PAGE = 5;
    private const MAX_PER_PAGE     = 100;

    public function rules(): array
    {
        return [
            'status'   => ['nullable', Rule::enum(MovieStatus::class)],
            'sort_by'  => ['nullable', Rule::enum(WatchlistMovieSortBy::class)],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'search'   => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
        ];
    }

    public function filters(): array
    {
        $sortBy = $this->validated('sort_by');

        return [
            'status'   => $this->validated('status'),
            'sort_by'  => $sortBy ? WatchlistMovieSortBy::from($sortBy) : WatchlistMovieSortBy::Status,
            'sort_dir' => $this->validated('sort_dir', 'desc'),
            'search'   => $this->validated('search'),
            'per_page' => (int) ($this->validated('per_page') ?? self::DEFAULT_PER_PAGE),
        ];
    }
}
