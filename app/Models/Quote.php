<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'quote_user');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
