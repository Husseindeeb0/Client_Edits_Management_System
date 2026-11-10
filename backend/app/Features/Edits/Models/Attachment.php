<?php

namespace App\Features\Edits\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'edit_id',
        'file_path',
        'original_file_name',
        'description',
    ];

    /**
     * Get the edit that owns the attachment.
     */
    public function edit(): BelongsTo
    {
        return $this->belongsTo(Edit::class);
    }
}

