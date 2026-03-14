<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class LocationAtRequest extends FormRequest
{
    public function rules(): array
    {
        return ['at' => ['required', 'date']];
    }

    public function at(): Carbon
    {
        return Carbon::parse($this->input('at'));
    }
}
