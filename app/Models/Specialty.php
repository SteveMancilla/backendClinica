<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function studies(): HasMany
    {
        return $this->hasMany(Study::class);
    }

    public function medicalAttentions(): HasMany
    {
        return $this->hasMany(MedicalAttention::class);
    }
}
