<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id','project_id','partner_id',
        'currency','amount','status','due_date','paid_at','method',
        'notes','receipt_path'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public const STATUS = ['pending','scheduled','paid','canceled'];

    public function report()  { return $this->belongsTo(Report::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
}
