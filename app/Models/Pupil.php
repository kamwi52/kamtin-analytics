<?php
// In app/Models/Pupil.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pupil extends Model
{
    use HasFactory;

    // Add this to allow mass assignment from the CSV import
    protected $guarded = [];

    // Define the primary key if it's not 'id'
    protected $primaryKey = 'pupil_db_id';

    /**
     * Get all of the results for the Pupil.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'pupil_db_id'); // You may need to create the Result model
    }
}