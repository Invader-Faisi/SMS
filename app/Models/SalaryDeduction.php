<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'name',
        'amount',
    ];

    public function salaries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Salary::class, 'salary_deduction_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('type', 'LIKE', "%{$value}%")
                    ->orWhere('name', 'LIKE', "%{$value}%")
                    ->orWhere('category', 'LIKE', "%{$value}%");
            });
        }

    }
}
