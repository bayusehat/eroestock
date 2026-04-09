<?php

namespace App\Livewire\Brand;

use App\Models\Brand;
use Livewire\Component;

class Show extends Component
{
    public Brand $brand;

    public function mount(Brand $brand): void
    {
        $this->brand = $brand;
    }

    public function render()
    {
        return view('livewire.brands.show');
    }
}
