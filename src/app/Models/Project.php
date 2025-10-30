<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by','title','description','date_release','finished','url','steam_id','capsule'
    ];
    protected $casts = [
        'date_release' => 'date',
        'finished' => 'boolean',
        'steam_id' => 'integer',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function partners() {
        return $this->belongsToMany(Partner::class, 'project_partner')
            ->withPivot(['share_percent','role','valid_from','valid_until'])
            ->withTimestamps();
    }

    public function reportLinks() {
        return $this->hasMany(ReportProject::class);
    }

    public function payouts() {
        return $this->hasMany(Payout::class);
    }
}
