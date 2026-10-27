<?php

use App\Models\Event;
use App\Models\Type;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    #[Url]
    public $sortBy = '';
    #[Url]
    public $sortDirection = 'desc';

    public Collection $types;
    public int $selectedType = 0;



    #[Computed]
    public function events()
    {
        return Event::query()->with('type')
            ->whereHas('type', fn($query) => $query->where('archived', true))
            ->when($this->selectedType > 0, fn($query) => $query->where('type_id', $this->selectedType))
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)->simplePaginate(5);
    }

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function mount(): void
    {
        $this->types = Type::where('archived', true)->get();
    }


};
?>

<div class="mx-auto max-w-4xl flex flex-col gap-4">
    <flux:heading level="3" size="xl" class="text-center font-semibold">Archived Events</flux:heading>
    <flux:select
        variant="listbox"
        wire:model.live="selectedType"
        placeholder="select type name"
        :disabled="$types->isEmpty()"
        class="w-fit self-end"
    >
        <flux:select.option value="0">All types</flux:select.option>
        @foreach($types as $type)
            <flux:select.option value="{{$type->id}}" :key="$type->id">{{$type->name}}</flux:select.option>
        @endforeach
    </flux:select>
    @if($this->events->isEmpty())
        <flux:text>No events in DB</flux:text>
    @else
        <flux:table :paginate="$this->events">
            <flux:table.columns>
                <flux:table.column class="w-full">Name (description)</flux:table.column>
                <flux:table.column class="w-px whitespace-nowrap">Amount</flux:table.column>
                <flux:table.column class="w-px whitespace-nowrap" align="center"
                                   sortable
                                   :sorted="$sortBy === 'event_date'"
                                   :direction="$sortDirection"
                                   wire:click="sort('event_date')">Date
                </flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->events as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell>{{$item->type->name}}@if($item->description !== null)
                                <br><small>{{$item->description}}</small>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            {{$item->amount}}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{$item->dateFormatted}}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
