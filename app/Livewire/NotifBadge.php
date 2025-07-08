<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notif;
use Illuminate\Support\Facades\Auth;

class NotifBadge extends Component
{
    public $count = 0;

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

    public function markAsReadAndRedirect($notifId)
    {
        $notif = Notif::find($notifId);

        if ($notif && $notif->id_penerima === Auth::id()) {
            $notif->status = 'terbaca';
            $notif->save();

            // Jika role adalah konselor, arahkan ke konseling
            if (Auth::user()->role === 'KONSELOR') {
                return redirect()->route('konseling', ['id' => $notif->id_order]);
            }

            // Jika keterangan mengandung kata 'LUNAS', arahkan ke konseling
            if (stripos($notif->keterangan, 'LUNAS') !== false) {
                return redirect()->route('konseling', ['id' => $notif->id_order]);
            }

            // Default: arahkan ke halaman orders
            return redirect()->route('orders', ['id' => $notif->id_order]);
        }

        return null;
    }


    public function render()
    {
        $this->updateCount();

        return view('livewire.notif-badge', [
            'notifs' => Notif::where('id_penerima', Auth::id())
                ->where('status', 'terkirim')
                ->latest()->limit(10)->get(),
            'allnotifs' => Notif::where('id_penerima', Auth::id())
                ->where('status', 'terbaca')
                ->latest()->limit(10)->get(),
        ]);
    }
}
