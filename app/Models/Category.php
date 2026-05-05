<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    protected $table = 'user_to_category';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_to_category', 'category_id', 'user_id')
                    ->withPivot('category_root_id', 'is_main');
    }
}
