<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InvoiceReport extends Pivot
{
    protected $table = 'invoice_report';
    protected $fillable = ['invoice_id','report_id'];
}
