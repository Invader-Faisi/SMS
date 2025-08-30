<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = [
        'staff_id',
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
            ->orWhere('staff_id','LIKE',"%{$value}%");

    }
}
