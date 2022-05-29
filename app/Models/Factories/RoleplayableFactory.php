<?php

namespace App\Models\Factories;

use App\Models\Contracts\Roleplayable;
use App\Models\Organisation;
use App\Models\Pays;
use App\Models\Ville;

class RoleplayableFactory
{
    use ModelFactory;

    /**
     * Interface implémentée par les modèles roleplayables.
     */
    public const contract = Roleplayable::class;

    /**
     * Liste des classes roleplayables.
     */
    public const models = [
        Organisation::class,
        Pays::class,
        Ville::class,
    ];
}
