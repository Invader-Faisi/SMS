<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'basic_salary',
        'house_allowance',
        'medical_allowance',
        'transport_allowance',
        'other_allowance',
    ];

    public function salaries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Salary::class, 'salary_structure_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('type', 'LIKE', "%{$value}%")
                    ->orWhere('category', 'LIKE', "%{$value}%");
            });
        }

    }
}
