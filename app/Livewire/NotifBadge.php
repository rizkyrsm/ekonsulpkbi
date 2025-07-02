<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notif;
use Illuminate\Support\Facades\Auth;

class NotifBadge extends Component
{
    public $count = 0;

    protected $listeners = ['notifReceived' => 'updateCount'];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $this->count = Notif::where('id_penerima', Auth::id())
                            ->where('status', 'terkirim')
                            ->count();
    }

    public function render()
    {
        return view('livewire.notif-badge');
    }
}
