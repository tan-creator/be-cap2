<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'gender',
        'avatar'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
