<?php

use App\Models\User;
use App\Models\Url;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::public')] class extends Component
{
    public $name;
    public string $description;
    public string $profilePhoto;
    public string $background; 
    public $urls = [];

    public function mount($name)
    {
        $this->name = $name;

        $user = User::where('name', $name)->firstOrFail();

        $this->description = $user->description ?? '';
        $this->profilePhoto = $user->photo ?? '';
        $this->background = $user->background  ?? '';

        $this->urls = Url::where('user_id', $user->id)->get()->keyBy('id')->toArray();
    }
    
    public function render()
    {
        return $this->view()
            ->title($this->name); 
    }
    };
?>

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4" 
            style="
                background-image: url('{{ asset($background) }}');
                background-color: {{ $background }};
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            "
        >
            <div class="flex flex-col items-center text-center">                 
                <flux:avatar circle size="xl" :src="asset($profilePhoto)" />

                <flux:heading class="mt-4" size="xl">{{ $name }}</flux:heading>
                <flux:text class="mb-4">{{$description }}</flux:text>

                <div class="grid gap-4">
                @foreach ($urls as $id => $url)
                    <flux:button target="_blank" :href="$url['url']">{{ $url['text'] }}</flux:button>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>