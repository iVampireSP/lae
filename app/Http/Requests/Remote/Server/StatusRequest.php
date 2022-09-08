<?php

namespace App\Http\Requests\Remote\Server;

use App\Models\Server\Status;
use Anik\Form\FormRequest;

class StatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $server = $this->route('server');

        return $server->query()->where('module_id', auth('remote')->id())->exists();
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
