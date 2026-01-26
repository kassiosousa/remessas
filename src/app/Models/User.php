<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory, HasUuids;

    protected $fillable = ['name','email','password','type'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function createdReports() {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function createdProjects() {
        return $this->hasMany(Project::class, 'created_by');
    }
}
