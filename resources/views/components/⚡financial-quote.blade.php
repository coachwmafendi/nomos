<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;

new class extends Component
{
    public $quote = 'Loading...';
    public $author = '';

    public function mount()
    {
        $this->fetchQuote();
    }

    public function fetchQuote()
    {
        try {
            $response = Http::timeout(5)->get('https://v2.jokeapi.dev/joke/Any?type=single&contains=money', 
              [ 'blacklistFlags' => 'nsfw,religious,political,sexist,explicit',
                'type' => 'single'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->quote = $data['joke'] ?? 'Save more than you spend!';
                $this->author = '💰 Money Tip';
            }
        } catch (\Exception $e) {
            $this->quote = 'A penny saved is a penny earned.';
            $this->author = 'Benjamin Franklin';
        }
    }

    #[On('refresh-quote')]
    public function refresh()
    {
        $this->fetchQuote();
    }
}
?>

<div class="bg-linear-to-r from-emerald-50 to-blue-50 p-6 rounded-xl border border-emerald-200">
    <flux:card>
        <div class="text-center">
             <div class="text-xl text-yellow-500">{{ $author }}</div>

            <p class="text-white-700 italic leading-relaxed mb-4 px-4" style="font-size: 0.95rem;">
                "{{ $quote }}"
            </p>
        </div>
        <div class="flex justify-center mt-4">

        <flux:button 
            wire:click="fetchQuote" 
            variant="outline" 
            size="sm" 
            class="mt-4 mx-auto block">
            New Tip
        </flux:button>
        </div>
    </flux:card>
</div>