<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectPartner extends Pivot
{
    protected $table = 'project_partner';
    protected $fillable = ['project_id','partner_id','share_percent','role','valid_from','valid_until'];
    protected $casts = [
        'share_percent' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];
}
    