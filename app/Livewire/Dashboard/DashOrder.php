<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashOrder extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 5;

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Ambil data order berdasarkan user yang sedang login dan pencarian
        $orders = Order::where('id_user', Auth::id())
            ->where(function ($query) {
                $query->where('nama_layanan', 'like', '%' . $this->search . '%')
                      ->orWhere('voucher', 'like', '%' . $this->search . '%')
                      ->orWhere('payment_status', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.dashboard.order', compact('orders'));
    }
}
