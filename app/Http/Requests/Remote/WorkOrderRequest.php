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
    public function authorize(): bool
    {
        $work_order = $this->route('workOrder');

        // if work_order is model
        if ($work_order instanceof WorkOrder) {
            $work_order_id = $work_order->id;
        } else {
            $work_order_id = $work_order;
        }

        return WorkOrder::where('id', $work_order_id)->where('module_id', auth('module')->id())->exists();
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
