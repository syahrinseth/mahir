<?php

namespace App\Modules\Subscription\Filament\Resources\Subscriptions\Schemas;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->label('Tenant')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('plan')
                    ->options(collect(PlanType::cases())->mapWithKeys(
                        fn (PlanType $plan): array => [$plan->value => $plan->label()]
                    )->all())
                    ->required(),
                Select::make('status')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                        fn (SubscriptionStatus $status): array => [$status->value => $status->label()]
                    )->all())
                    ->required(),
                DateTimePicker::make('trial_ends_at'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
            ]);
    }
}
