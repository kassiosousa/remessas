<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['created_by','code','date_created','currency','total_amount','notes'];
    protected $casts = [
        'date_created' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reports() {
        return $this->belongsToMany(Report::class, 'invoice_report')->withTimestamps();
    }
}
