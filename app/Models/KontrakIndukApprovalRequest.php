<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakIndukApprovalRequest extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk_approval_request';
    protected $guarded = ['id'];

    protected $casts = [
        'data_lama'       => 'array',
        'payload_request' => 'array',
    ];

    public function kontrakinduk(): BelongsTo
    {
        return $this->belongsTo(Kontrakinduk::class, 'kontrakinduk_id');
    }

    public function adendum(): BelongsTo
    {
        return $this->belongsTo(KontrakIndukAdendum::class, 'adendum_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
