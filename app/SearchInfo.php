<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SearchInfo extends Model
{
    protected $table = 'searched_info';
    protected $primaryKey = 'id';
    protected $fillable = ['user_name', 'searched_at', 'keyword', 'start', 'end', 'created_at', 'updated_at'];
}
