<?php

namespace App\Modules\Subscription\Http\Requests;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /** The ID of the tenant this subscription belongs to. */
            'tenant_id' => ['required', 'integer', 'exists:landlord.tenants,id'],
            /** The subscription plan type. */
            'plan' => ['required', 'string', Rule::enum(PlanType::class)],
            /** The initial status of the subscription. Defaults to "trial" if not provided. */
            'status' => ['sometimes', 'string', Rule::enum(SubscriptionStatus::class)],
            /** The date and time when the trial period ends. */
            'trial_ends_at' => ['nullable', 'date'],
            /** The date and time when the subscription starts. */
            'starts_at' => ['nullable', 'date'],
            /** The date and time when the subscription ends. */
            'ends_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Tenant is required.',
            'tenant_id.exists' => 'The selected tenant does not exist.',
            'plan.required' => 'Plan is required.',
            'plan.Illuminate\Validation\Rules\Enum' => 'The selected plan is invalid.',
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid.',
        ];
    }
}
