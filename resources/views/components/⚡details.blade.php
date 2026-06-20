<?php

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Url;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $description;
    public string $profilePhoto;
    public string $background; 

    #[Validate('image|max:2048')]
    public $photo;
    #[Validate('image|max:2048')]
    public $image;

    public string $color = '';
    public string $last = '';
    public $path;
    public $imgPath;

    #[Validate('required|min:1')]
    public string $text;
    #[Validate('required|min:1')]
    public string $url;
    public $urls = [];

    public function setBackground(string $bg)
    {
        $this->last = $bg;
        if($bg === 'image')
        {
            $this->color = '';
        }
        if($bg === 'color')
        {
            $this->image = '';
        }
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $bg = Auth::user()->background;

        $this->name = Auth::user()->name;
        $this->description = Auth::user()->description ?? '';
        $this->profilePhoto = Auth::user()->photo ?? '';
        $this->background = $bg  ?? '';
        $this->path = Auth::user()->photo ?? '';
        $this->last = Str::length($bg) === 7 ? 'color' : 'image';
        if(Str::length($bg) === 7)
        {
            $this->color = $bg;
        }

        $this->urls = Url::where('user_id', Auth::id())->get()->keyBy('id')->toArray();
    }

    public function update()
    {
        $user = Auth::user();

        if($this->photo !== null)
        {
            $this->path = $this->photo->store('profile-photos', 'public');
        }

        if($this->last === 'image')
        {
            $this->imgPath = $this->image->store('backgrounds', 'public');
        }
        
        $user->fill([
            'name' => $this->name,
            'description' => $this->description,
            'photo' =>  $this->path,
            'background' =>  $this->imgPath === null ? $this->color :  $this->imgPath
        ]);

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function save()
    {
        Url::create([
            'text' => $this->text,
            'url' => $this->url,
            'user_id' => Auth::user()->id
        ]);

        Flux::toast(variant: 'success', text: __('Link added.'));
    }

    public function delete(int $id)
    {
       Url::where('user_id', Auth::id())
        ->findOrFail($id)
        ->delete();
    }

    public function edit(int $id)
    {
        Url::where('user_id', Auth::id())
        ->findOrFail($id)
        ->update([
            'text' => $this->urls[$id]['text'],
            'url' => $this->urls[$id]['url']
        ]);
    }
};
?>

<div class="flex h-full gap-4">
    <div class="flex-1 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
        <flux:heading size="lg" class="text-center">Details</flux:heading>
        <form wire:submit="update" class="flex flex-col gap-4 mb-4">
            <flux:field >
                <flux:label>Your URL</flux:label>
                <flux:description>It's the same as your name</flux:description>
                <flux:input.group>
                    <flux:input.group.prefix>urlist.test/</flux:input.group.prefix>
                    <flux:input wire:model.live="name" placeholder="your name" />
                    <flux:button class="p-4" target="_blank" :href="config('app.url') . '/'. $name" color="blue", variant="primary" icon="arrow-up-right"/>
                </flux:input.group>
            </flux:field>
            <flux:textarea wire:model.live="description" label="Description" placeholder="These are my URLs..." rows="auto" resize="vertical"/>

            <flux:input wire:model="photo" type="file" label="Profile photo"/>

            <flux:field class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <flux:label>Background</flux:label>
                <flux:description>Choose one</flux:description>
                <flux:input wire:click="setBackground('image')" wire:model.live="image" type="file" label="Image"/>
                <flux:separator text="or"/>
                <flux:input wire:click="setBackground('color')" wire:model.live="color" type="color" label="Color"/>
            </flux:field>

            <flux:button type="submit" variant="primary">Save changes</flux:button>
        </form>

        <flux:separator text="Adding links"/>

        <flux:input.group class="mt-4 mb-4">
            <flux:input wire:model="text" placeholder="Text" />
            <flux:input wire:model="url" placeholder="URL" />
            <flux:button wire:click="save" icon="plus">Add</flux:button>
        </flux:input.group>

        <flux:separator text="Editing links"/>

        <div class="grid gap-2 mt-4">
            @foreach ($urls as $id => $url)
            <div class="flex items-center gap-2">
                <flux:input.group>
                    <flux:input.group.prefix>{{ $loop->index + 1 }} </flux:input.group.prefix>
                    <flux:input wire:model="urls.{{ $id }}.text" placeholder="Text" />
                    <flux:input wire:model="urls.{{ $id }}.url" placeholder="URL" />
                    <flux:button class="p-4" wire:click="edit({{ $id }})" color="blue", variant="primary" icon="pencil-square"/>
                    <flux:button class="p-4" wire:click="delete({{ $id }})" wire:confirm="Are you sure you want to delete this link?" icon="trash" color="red" variant="primary"/>
                </flux:input.group>
            </div>
            @endforeach
        </div>
    </div>    

    <div class="flex-1 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4" 
        style="
        @if($image)
            background-image: url('{{ $image->temporaryUrl() }}');
        @elseif($last === 'image')
            background-image: url('{{ asset($background) }}');
        @endif
        @if(!blank($background) && blank($color))
            background-color: {{ $background }};
        @else
            background-color: {{ $color }};
        @endif
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        "
    >
        <div class="flex flex-col items-center text-center">
            @if ($photo)
                <flux:avatar circle size="xl" :src="$photo->temporaryUrl()" />
            @else                    
                <flux:avatar circle size="xl" :src="asset($this->profilePhoto)" />
            @endif

            <flux:heading class="mt-4" size="xl">{{ $name }}</flux:heading>
            <flux:text class="mb-4">{{ $description }}</flux:text>

            <div class="grid gap-4">
            @foreach ($urls as $id => $url)
                <flux:button target="_blank" :href="$url['url']">{{ $url['text'] }}</flux:button>
            @endforeach
            </div>
        </div>
    </div>
</div>