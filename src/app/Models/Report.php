<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by','title','platform','period_month','currency',
        'gross_amount','fees','taxes','net_amount','statement_ref'
    ];
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'fees'         => 'decimal:2',
        'taxes'        => 'decimal:2',
        'net_amount'   => 'decimal:2',
    ];

    public const PLATFORMS = ['steam','epic','xbox','playstation','switch','android','ios','itch'];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects() {
        return $this->hasMany(ReportProject::class);
    }

    public function payouts() {
        return $this->hasMany(Payout::class);
    }
}
