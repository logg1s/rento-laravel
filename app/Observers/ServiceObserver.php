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
        // Xóa cache của service này
        Cache::forget('service:' . $service->id);

        // Xóa các cache liên quan đến danh sách service
        Cache::tags(['services'])->flush();
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        // Xóa cache của service này
        Cache::forget('service:' . $service->id);

        // Xóa các cache liên quan đến danh sách service
        Cache::tags(['services'])->flush();
    }
}
