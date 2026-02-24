<?php

namespace App\Modules\Subscription\Http\Requests;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
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
            /** The subscription plan type. */
            'plan' => ['sometimes', 'string', Rule::enum(PlanType::class)],
            /** The status of the subscription. */
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
            'plan.Illuminate\Validation\Rules\Enum' => 'The selected plan is invalid.',
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid.',
        ];
    }
}
