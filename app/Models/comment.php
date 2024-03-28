<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'task_id'
    ];


    public function task()
    {
        return $this->belongsTo(Tasks::class);
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
