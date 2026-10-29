<?php

use App\Models\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Types - Event Counter')]
class extends Component {


    public string $name = '';

    public string $selectedColor = 'gray';

    #[Locked]
    public ?int $typeId = null;

    public ?Type $type = null;


    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'selectedColor' => ['required', Rule::in(array_keys(Type::COLORS))],
        ];
    }

    #[Computed]
    public function types(): Collection
    {
        return Type::all();
    }

    public function toggleArchived(Type $type): void
    {
        $type->update(['archived' => !$type->archived]);

        unset($this->types);
    }

    public function delete(Type $type): void
    {
        $type->delete();
    }

    public function edit(Type $type): void
    {
        Flux::modal('type-form')->show();

        $this->type = $type;
        $this->typeId = $type->id;
        $this->name = $type->name;
        $this->selectedColor = $type->selected_color;

    }

    public function save(): void
    {
        $this->validate();
        if (empty($this->type)) {
            Type::create(['name' => $this->name, 'selected_color' => $this->selectedColor]);
        } else {
            $this->type->update(['name' => $this->name, 'selected_color' => $this->selectedColor]);
        }

        $this->reset('type', 'name', 'selectedColor');
        unset($this->types);
        Flux::modal('type-form')->close();

    }

    public function closeModal()
    {
        $this->reset('type', 'name', 'selectedColor');
    }
};
?>

<div class="mx-auto max-w-4xl flex flex-col gap-4">

    <flux:heading level="3" size="xl" class="text-center font-semibold text-accent-content">Types</flux:heading>

    <flux:card variant="muted" body="flush">
        <flux:card.header class="py-4">
            <flux:card.heading>Types: <b>{{count($this->types)}}</b></flux:card.heading>

            <flux:card.actions>
                <flux:modal.trigger name="type-form" class="flex justify-end">
                    <flux:button type="button" class="w-fit cursor-pointer" variant="primary">Create Type</flux:button>
                </flux:modal.trigger>
            </flux:card.actions>
        </flux:card.header>

        <flux:card.body>
            <flux:table bleed>
                <flux:table.columns>
                    <flux:table.column class="w-px whitespace-nowrap py-4!"></flux:table.column>
                    <flux:table.column class="w-full py-4! font-bold!">Name</flux:table.column>
                    <flux:table.column class="w-px whitespace-nowrap py-4! font-bold!">Archived</flux:table.column>
                    <flux:table.column class="w-px whitespace-nowrap py-4!"></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->types as $type)
                        <flux:table.row :key="$type->id">
                            <flux:table.cell>
                                <div class="size-3 rounded-full {{ $type->colorClass }}"></div>
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-700">{{$type->name}}</flux:table.cell>
                            <flux:table.cell align="center">
                                <flux:switch :checked="$type->archived" wire:change="toggleArchived({{$type->id}})"/>
                            </flux:table.cell>
                            <flux:table.cell align="center" class="**:cursor-pointer flex gap-0 lg:gap-1">
                                <flux:button icon="trash"
                                             wire:confirm="Do you want to delete type?"
                                             wire:click="delete({{$type->id}})"
                                             variant="ghost"
                                />
                                <flux:button icon="square-pen"
                                             wire:click="edit({{$type->id}})"
                                             variant="ghost"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell variant="strong" colspan="4" align="center">
                                No types in DB
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card.body>
    </flux:card>


    @if($this->types->isEmpty())
        <flux:text>No types in DB</flux:text>
    @else

    @endif
    <flux:modal name="type-form" class="w-2/3 space-y-6" @close="closeModal">
        <flux:heading>{{$this->type ? "Edit Type" : "Create Type"}}</flux:heading>
        <flux:separator/>
        <form wire:submit="save()" autocomplete="off" class="space-y-6">
            <flux:input type="text" label="Name" wire:model="name" badge="required"/>

            <flux:label badge="required">Type Color</flux:label>
            <flux:select variant="listbox" placeholder="select color..." wire:model="selectedColor" badge="required">
                @foreach(Type::COLORS as $key => $class)
                    <flux:select.option :value="$key" :key="$key">
                        <div class="flex items-center gap-2">
                            <div class="rounded-full size-4 {{$class}}"></div>
                            {{ucfirst($key)}}
                        </div>
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="selectedColor"/>
            <flux:button type="submit" variant="primary" class="cursor-pointer">Save</flux:button>
        </form>
    </flux:modal>
</div>
