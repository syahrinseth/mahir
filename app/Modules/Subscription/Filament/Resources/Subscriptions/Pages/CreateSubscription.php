<?php

namespace App\Modules\Subscription\Filament\Resources\Subscriptions\Pages;

use App\Modules\Subscription\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;
}
