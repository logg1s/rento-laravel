<?php

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class ServiceObserver
{
    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        Cache::forget('service:' . $service->id);


        Cache::tags(['services'])->flush();
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        Cache::forget('service:' . $service->id);


        Cache::tags(['services'])->flush();
    }
}
