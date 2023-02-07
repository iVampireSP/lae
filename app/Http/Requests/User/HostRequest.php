<?php

namespace App\Http\Requests\User;

use App\Models\Host;
use Illuminate\Foundation\Http\FormRequest;

class HostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $host = $this->route('host');

        if (!($host instanceof Host)) {
            $host = (new Host)->where('id', $host)->first();
        }

        if ($host->user_id ?? 0 == $this->user()->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
