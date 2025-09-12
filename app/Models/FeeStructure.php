<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $fillable = [
        'name',
        'class',
        'amount',
        'type',
        'frequency',
    ];

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'LIKE', "%{$value}%")
                    ->orWhere('type', 'LIKE', "%{$value}%")
                    ->orWhere('frequency', 'LIKE', "%{$value}%");
            });
        }

    }
}
