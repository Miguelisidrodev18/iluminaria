<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'etiqueta',
        'grupo',
        'descripcion',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permiso');
    }
}
