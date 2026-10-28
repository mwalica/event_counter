<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = ['type_id', 'description', 'amount', 'event_date'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime'
        ];
    }

    protected function dateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->event_date)->format('j M Y')
        );
    }
}
