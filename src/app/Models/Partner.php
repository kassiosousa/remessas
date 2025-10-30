<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = ['created_by','name','email','portfolio','birthday'];
    protected $casts = ['birthday' => 'date'];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects() {
        return $this->belongsToMany(Project::class, 'project_partner')
            ->withPivot(['share_percent','role','valid_from','valid_until'])
            ->withTimestamps();
    }

    public function payouts() {
        return $this->hasMany(Payout::class);
    }
}
