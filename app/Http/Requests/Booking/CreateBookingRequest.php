<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location' => 'required|string|max:255',
            'lesson_period' => 'required|integer|between:1,9',
            'start_time' => [
                'required',
                'date',
                'after:now',
            ],
            'end_time' => 'required|date|after:start_time',
            'is_evaluation_period' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
