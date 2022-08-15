<?php

namespace App\Http\Requests\Remote;

use App\Models\WorkOrder\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;

class WorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return WorkOrder::where('id', $this->route('work_order')->id)->where('module_id', auth('remote')->id())->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
