<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleMapping extends Model
{
    protected $fillable = ['tender_description', 'acIdent', 'acName'];
}