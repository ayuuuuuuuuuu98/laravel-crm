<?php

namespace Webkul\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppAccount extends Model
{
    protected $table = 'whatsapp_accounts';

    protected $fillable = [
        'name',
        'provider',
        'meta_app_id',
        'meta_app_secret',
        'business_account_id',
        'phone_number_id',
        'access_token',
        'verify_token',
        'status',
        'settings',
        'user_id',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
