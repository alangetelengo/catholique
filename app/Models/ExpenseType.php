<?php

namespace App\Models;

use Database\Factories\ExpenseTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    /** @use HasFactory<ExpenseTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'description',
        'actif',
        'ordre',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
