<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = ['name', 'archived', 'selected_color'];

    protected function casts(): array
    {
        return [
            'archived' => 'boolean'
        ];
    }

    public const COLORS = [
        'gray' => 'bg-zinc-300 dark:bg-zinc-500',
        'red' => 'bg-red-400 dark:bg-red-600',
        'green' => 'bg-green-400 dark:bg-green-500',
        'blue' => 'bg-blue-400 dark:bg-blue-500',
        'orange' => 'bg-orange-400 dark:bg-orange-500',
        'fuchsia' => 'bg-fuchsia-400 dark:bg-fuchsia-500',
    ];

    protected function colorClass(): Attribute
    {
        return Attribute::make(
            get: fn() => self::COLORS[$this->selected_color ?? self::COLORS['gray']],
        );
    }
}
