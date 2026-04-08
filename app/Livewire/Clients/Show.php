<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;

class Show extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function render()
    {
        return view('livewire.clients.show');
    }
}
