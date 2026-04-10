<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        $tipos = ['ARQ', 'ING', 'DIS', 'PN', 'PJ'];

        $apellidos = $this->faker->lastName();
        $nombres   = $this->faker->firstName();

        return [
            // Legacy
            'tipo_documento'   => 'DNI',
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'nombre'           => $apellidos . ' ' . $nombres,
            'estado'           => 'activo',
            // Nuevos
            'apellidos'        => $apellidos,
            'nombres'          => $nombres,
            'celular'          => '9' . $this->faker->numerify('########'),
            'dni'              => $this->faker->unique()->numerify('########'),
            'tipo_cliente'     => $this->faker->randomElement($tipos),
            'fecha_registro'   => now()->toDateString(),
            'registrado_por'   => $this->faker->name(),
            'correo_personal'  => $this->faker->safeEmail(),
            'empresa'          => $this->faker->optional()->company(),
            'etiquetas'        => [],
            'acepta_whatsapp'  => true,
        ];
    }

    /**
     * Cliente con etiqueta Mamá para pruebas de difusión.
     */
    public function mama(): static
    {
        return $this->state(['etiquetas' => ['Mamá', 'Mujer']]);
    }

    /**
     * Cliente con etiqueta Papá.
     */
    public function papa(): static
    {
        return $this->state(['etiquetas' => ['Papá', 'Hombre']]);
    }

    /**
     * Cliente sin WhatsApp habilitado.
     */
    public function sinWhatsapp(): static
    {
        return $this->state(['acepta_whatsapp' => false]);
    }
}
