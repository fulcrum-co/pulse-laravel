<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoLead extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'org_name',
        'org_url',
        'org_size',
        'org_size_note',
        'source',
        'source_url',
        'ip_address',
        'user_agent',
    ];
}
