<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'content',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
