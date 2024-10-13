<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FavoritePlace extends Model
{
    use HasFactory;
     public $timestamps = false;
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    protected $fillable = [
        'name', 
        'place_id',
        'latitude',
        'longitude',
        'prefecture',
        'area',
        'comment'
    ];
    
}