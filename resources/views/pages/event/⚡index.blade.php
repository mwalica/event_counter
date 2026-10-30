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

new #[Title('Event Counter')]
class extends Component {

    use WithPagination;


    #[Url]
    public $sortBy = '';
    #[Url]
    public $sortDirection = 'desc';

    public Collection $types;
    public int $selectedType = 0;

    #[Validate('required|integer|min:1')]
    public ?int $type_id = null;
    #[Validate('nullable|string|min:3')]
    public ?string $description = null;
    #[Validate('required|integer|min:1')]
    public int $amount = 1;
    #[Validate('required')]
    public string $eventDate;

    #[Locked]
    public ?int $eventId = null;

    public ?Event $event = null;

    private function filteredEvents(): Builder
    {
        return Event::query()
            ->whereHas('type', fn($query) => $query->where('archived', false))
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
        $this->types = Type::where('archived', false)->get();
        $this->eventDate = Carbon::now()->toDateString();
    }

    private function resetForm(): void
    {
        $this->reset('event', 'type_id', 'description', 'amount');
        $this->eventDate = Carbon::now()->toDateString();
        $this->resetValidation();
    }

    public function delete(Event $event)
    {
        $event->delete();
    }

    public function edit(Event $event)
    {
        Flux::modal('event-form')->show();

        $this->event = $event;
        $this->eventId = $event->id;
        $this->type_id = $event->type->id;
        $this->description = $event->description ?? '';
        $this->amount = $event->amount;
        $this->eventDate = $event->event_date->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();
        if (empty($this->event)) {
            Event::create([
                'type_id' => $this->type_id,
                'description' => $this->description ?: null,
                'amount' => $this->amount,
                'event_date' => $this->eventDate
            ]);
        } else {
            $this->event->update([
                'type_id' => $this->type_id,
                'description' => $this->description ?: null,
                'amount' => $this->amount,
                'event_date' => $this->eventDate
            ]);
        }

        $this->resetForm();
        unset($this->events);
        Flux::modal('event-form')->close();
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

};
?>

<div class="mx-auto max-w-4xl flex flex-col gap-4">
    <flux:heading level="3" size="xl" class="text-center font-semibold text-accent-content">Events</flux:heading>

    <flux:card variant="muted" body="flush">
        <flux:card.header class="py-4">
            <flux:card.heading>Events: <b>{{$this->totalAmount}}</b></flux:card.heading>

            <flux:card.actions>
                <flux:select
                    variant="listbox"
                    wire:model.live="selectedType"
                    placeholder="select type name"
                    :disabled="$types->isEmpty()"
                    class="w-fit"
                    aria-label="Filter transactions"
                >
                    <flux:select.option value="0">All types</flux:select.option>
                    @foreach($types as $type)
                        <flux:select.option value="{{$type->id}}" :key="$type->id">{{$type->name}}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:modal.trigger name="event-form" class="flex justify-end">
                    <flux:button type="button" class="w-fit cursor-pointer" variant="primary"
                                 :disabled="$types->isEmpty()">Add Event
                    </flux:button>
                </flux:modal.trigger>
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
                    <flux:table.column class="w-px whitespace-nowrap py-4! hidden sm:table-cell"></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->events as $item)
                        <flux:table.row :key="$item->id">
                            <flux:table.cell><div class="size-2 sm:size-3 rounded-full {{$item->type->colorClass}}"></div></flux:table.cell>
                            <flux:table.cell class="text-zinc-700 text-xs! sm:text-sm!">
                                {{$item->type->name}}
                                @if($item->description !== null)
                                    <flux:text
                                        class="text-zinc-400 font-light text-xs">{{$item->description}}</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="center" class="text-zinc-700 text-xs! sm:text-sm!">
                                {{$item->amount}}
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-700 hidden sm:table-cell">
                                {{$item->dateFormatted}}
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-700 sm:hidden text-xs!">
                                {{$item->dateFormattedMobile}}
                            </flux:table.cell>
                            <flux:table.cell align="center" class="**:cursor-pointer gap-0 lg:gap-1 hidden sm:flex">
                                <flux:button icon="trash" variant="ghost" wire:confirm="Do you want to delete event?"
                                             wire:click="delete({{$item->id}})"/>
                                <flux:button icon="square-pen" variant="ghost" wire:click="edit({{$item->id}})"/>
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


    <flux:modal name="event-form" class="w-2/3 space-y-6" @close="closeModal">
        <flux:heading>{{$this->event ? "Edit Event" : "Create Event"}}</flux:heading>
        <flux:separator/>
        <form wire:submit="save()" autocomplete="off" class="space-y-6">
            <flux:field>
                <flux:label badge="required">Type:</flux:label>
                <flux:select variant="listbox" placeholder="Select type..." badge="required" wire:model="type_id">
                    @foreach($types as $type)
                        <flux:select.option value="{{$type->id}}">{{$type->name}}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="type_id"/>
            </flux:field>
            <flux:textarea label="Description" wire:model="description"/>
            <flux:input type="number" label="Amount" wire:model="amount" badge="required"/>
            <flux:date-picker wire:model="eventDate" label="Event date" badge="required"/>
            <flux:button type="submit" variant="primary" class="cursor-pointer">Save</flux:button>
        </form>
    </flux:modal>
</div>
