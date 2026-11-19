<?php

namespace App\Features\Clients\Models;

use App\Features\Companies\Models\Company;
use App\Features\Projects\Models\Project;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clients';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'last_follow_up_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_follow_up_date' => 'date',
        ];
    }

    /**
     * Get the companies associated with the client.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'client_id');
    }

    /**
     * Get the projects of all the client's companies.
     */
    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(Project::class, Company::class, 'client_id', 'company_id');
    }
}

