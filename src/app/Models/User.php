<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $fillable = ['name','email','password','type'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function createdReports() {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function createdProjects() {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function createdInvoices() {
        return $this->hasMany(Invoice::class, 'created_by');
    }
}
