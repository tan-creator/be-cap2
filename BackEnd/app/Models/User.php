<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PersonalTours;
use App\Models\Tours;
use App\Models\Rooms;
use App\Models\Favors;
use App\Models\Notifications;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

// use App\Models\Scopes\ExceptScope;
 
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'is_Admin',
        'user_roles',
        'about',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at'
    ];

    /**
     * Interact with the user's first name.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => $value !== '' ? Hash::make($value) : $value,
        );
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tours(){
        return $this->hasManyThrough(Tours::class, TSProfile::class, 'user_id', 'ts_id');
    }

    public function personal_tours(){
        return $this->hasMany(PersonalTours::class);
    }

    public function rooms(){
        return $this->belongsToMany(Rooms::class, 'members', 'user_id', 'room_id');
    }

    public function userProfile(){
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function tsProfile(){
        return $this->hasOne(TSProfile::class, 'user_id', 'id');
    }

    public function notifications(){
        return $this->hasMany(Notifications::class, 'receiver_id', 'id');
    }

    public function favors(){
        return $this->belongsToMany(Favors::class, 'favor_user', 'user_id', 'favor_id');
    }

    public function newUserInMonth($month, $year)
    {
        return self::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)->count();
    }

    public function numberUserWithRole($role = null)
    {
        $query = self::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as new_users'))
            ->where('created_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 YEAR)'));
        
        if ($role != null) {
            $query = $query->where('user_roles', $role);
        }

        $query = $query->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->mapWithKeys(function ($query) {
                return [$query['month'] => $query['new_users']];
            })
            ->toArray();
        
        return $query;
    }

    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

}
