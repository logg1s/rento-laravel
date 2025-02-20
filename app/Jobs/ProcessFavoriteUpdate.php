<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ProcessFavoriteUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }


    public function handle(): void
    {
        $user = User::find($this->data['user_id']);

        if (!$user) {
            return;
        }

        if ($this->data['action'] === 'attach') {
            $user->serviceFavorite()->attach($this->data['service_id']);
        } else {
            $user->serviceFavorite()->detach($this->data['service_id']);
        }
    }
}
