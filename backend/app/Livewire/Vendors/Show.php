<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Show extends Component
{
    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function render()
    {
        return view('livewire.vendors.show');
    }
}
