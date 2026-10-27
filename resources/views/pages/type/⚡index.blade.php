<?php

use App\Models\Type;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Types - EventCounter')]class extends Component {

    #[Validate('required|string|min:3')]
    public $name = '';

    #[Locked]
    public ?int $typeId = null;

    public ?Type $type = null;

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

    }

    public function save(): void
    {
        $this->validate();
        if (empty($this->type)) {
            Type::create(['name' => $this->name]);
        } else {
            $this->type->update(['name' => $this->name]);
        }

        $this->reset('type', 'name');
        unset($this->types);
        Flux::modal('type-form')->close();

    }

    public function closeModal()
    {
        $this->reset('type');
    }
};
?>

<div class="mx-auto max-w-4xl flex flex-col gap-4">
    <flux:heading level="3" size="xl" class="text-center font-semibold">Types</flux:heading>
    <flux:modal.trigger name="type-form" class="flex justify-end">
        <flux:button type="button" class="w-fit" variant="primary">Create Type</flux:button>
    </flux:modal.trigger>
    @if($this->types->isEmpty())
        <flux:text>No types in DB</flux:text>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-full">Name</flux:table.column>
                <flux:table.column class="w-px whitespace-nowrap">Archived</flux:table.column>
                <flux:table.column class="w-px whitespace-nowrap"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->types as $type)
                    <flux:table.row :key="$type->id">
                        <flux:table.cell variant="strong">{{$type->name}}</flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:switch :checked="$type->archived" wire:change="toggleArchived({{$type->id}})"/>
                        </flux:table.cell>
                        <flux:table.cell align="center" class="**:cursor-pointer flex gap-4">
                            <flux:button icon="trash" wire:confirm="Do you want to delete type?"
                                         wire:click="delete({{$type->id}})"/>
                            <flux:button icon="square-pen" wire:click="edit({{$type->id}})"/>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
    <flux:modal name="type-form" class="w-2/3 space-y-6" @close="closeModal">
        <flux:heading>{{$this->type ? "Edit Type" : "Create Type"}}</flux:heading>
        <flux:separator/>
        <form wire:submit="save()" autocomplete="off" class="space-y-6">
            <flux:input type="text" label="Name" wire:model="name" badge="required"/>
            <flux:button type="submit" variant="primary" class="cursor-pointer">Save</flux:button>
        </form>
    </flux:modal>
</div>
