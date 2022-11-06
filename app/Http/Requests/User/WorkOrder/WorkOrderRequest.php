<?php

namespace App\Http\Requests\User\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\WorkOrder\WorkOrder;

class WorkOrderReques extends FormRequest
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


        return WorkOrder::where('user_id', auth()->id())->where('id', $work_order_id)->exists();

        return false;
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
