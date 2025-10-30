<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportProject extends Model
{
    use HasFactory;

    protected $table = 'report_project';
    protected $fillable = [
        'project_id','report_id','units_sold','project_net_amount','currency'
    ];
    protected $casts = [
        'units_sold' => 'integer',
        'project_net_amount' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function report()  { return $this->belongsTo(Report::class); }
}
