<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function place(){
        return $this->belongsTo(Place::class);
    }
    
    public function getByLimit(int $limit_count = 2){
        return $this->orderBy('updated_at', 'DESC')->paginate($limit_count);
    }
}
