<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parents extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id',
        'image',
        'name',
        'cnic',
        'email',
        'password',
        'mobile',
        'address',
        'qualification',
        'designation',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id', 'parent_id');
    }

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        if (!empty($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'LIKE', "%{$value}%")
                    ->orWhere('parent_id', 'LIKE', "%{$value}%")
                    ->orWhere('cnic', 'LIKE', "%{$value}%");
            });
        }

    }
}
