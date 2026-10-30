<?php

use App\Models\Event;
use App\Models\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Archive - Event Counter')]
class extends Component {

    use WithPagination;

    #[Url]
    public $sortBy = '';
    #[Url]
    public $sortDirection = 'desc';

    public Collection $types;
    public int $selectedType = 0;

    private function filteredEvents(): Builder
    {
        return Event::query()
            ->whereHas('type', fn($query) => $query->where('archived', true))
            ->when($this->selectedType > 0, fn($query) => $query->where('type_id', $this->selectedType));
    }


    #[Computed]
    public function events()
    {
        $column = in_array($this->sortBy, Event::SORTABLE_COLUMNS, true) ? $this->sortBy : 'event_date';
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        return $this->filteredEvents()
            ->with('type')
            ->orderBy($column, $direction)
            ->simplePaginate(8);
    }

    #[Computed]
    public function totalAmount(): int
    {
        return (int) $this->filteredEvents()->sum('amount');
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
    <flux:heading level="3" size="xl" class="text-center font-semibold text-accent-content">Archived Events
    </flux:heading>

    <flux:card variant="muted" body="flush">
        <flux:card.header>
            <flux:card.heading>Events: <b>{{$this->totalAmount}}</b></flux:card.heading>

            <flux:card.actions>
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
            </flux:card.actions>
        </flux:card.header>

        <flux:card.body>
            <flux:table bleed>
                <flux:table.columns>
                    <flux:table.column class="w-px whitespace-nowrap py-4!"></flux:table.column>
                    <flux:table.column class="w-full py-4! font-bold!">Name (description)</flux:table.column>
                    <flux:table.column class="w-px whitespace-nowrap py-4! font-bold!">Amount</flux:table.column>
                    <flux:table.column class="w-px whitespace-nowrap py-4! font-bold!" align="center"
                                       sortable
                                       :sorted="$sortBy === 'event_date'"
                                       :direction="$sortDirection"
                                       wire:click="sort('event_date')">Date
                    </flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->events as $item)
                        <flux:table.row :key="$item->id">
                            <flux:table.cell><div class="size-3 rounded-full {{$item->type->colorClass}}"></div></flux:table.cell>
                            <flux:table.cell class="text-zinc-700">
                                {{$item->type->name}}
                                @if($item->description !== null)
                                    <flux:text
                                        class="text-zinc-400 font-light text-xs">{{$item->description}}</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="center" class="text-zinc-700 text-xs!">
                                {{$item->amount}}
                            </flux:table.cell>
                            <flux:table.cell class="hidden sm:table-cell text-zinc-700">
                                {{$item->dateFormatted}}
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-700 sm:hidden">
                                {{$item->dateFormattedMobile}}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell variant="strong" colspan="4" align="center">
                                No events in DB
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card.body>
    </flux:card>
    <flux:pagination :paginator="$this->events" class="border-0! -mt-2"/>
</div>
