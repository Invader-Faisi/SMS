<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'teacher_id',
        'image',
        'name',
        'email',
        'password',
        'mobile',
        'address',
        'qualification',
        'designation',
    ];

    #[Scope]
    public function scopeSearch(Builder $query,$value):void
    {
        $query->where('name','LIKE',"%{$value}%")
            ->orWhere('teacher_id','LIKE',"%{$value}%");

    }
}
