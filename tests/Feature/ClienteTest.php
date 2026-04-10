<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Setup helpers
    // ─────────────────────────────────────────────────────────────

    private function crearRol(string $nombre): Role
    {
        return Role::create(['nombre' => $nombre, 'descripcion' => $nombre]);
    }

    private function usuarioConRol(string $rolNombre): User
    {
        $role = Role::where('nombre', $rolNombre)->first()
            ?? $this->crearRol($rolNombre);

        return User::factory()->create([
            'role_id' => $role->id,
            'estado'  => 'activo',
        ]);
    }

    private function datosCliente(array $override = []): array
    {
        return array_merge([
            'tipo_documento'   => 'DNI',
            'numero_documento' => '12345678',
            'estado'           => 'activo',
            'apellidos'        => 'GARCIA',
            'nombres'          => 'Carlos',
            'celular'          => '987654321',
            'dni'              => '12345678',
            'tipo_cliente'     => 'ARQ',
            'acepta_whatsapp'  => true,
            'etiquetas'        => [],
        ], $override);
    }

    // ─────────────────────────────────────────────────────────────
    // Acceso y autenticación
    // ─────────────────────────────────────────────────────────────

    public function test_invitado_no_puede_ver_clientes(): void
    {
        $response = $this->get(route('clientes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_administrador_puede_ver_lista_clientes(): void
    {
        $admin = $this->usuarioConRol('Administrador');
        Cliente::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('clientes.index'));

        $response->assertOk();
        $response->assertViewIs('clientes.index');
        $response->assertViewHas('clientes');
    }

    public function test_vendedor_puede_ver_lista_clientes(): void
    {
        $vendedor = $this->usuarioConRol('Vendedor');

        $response = $this->actingAs($vendedor)->get(route('clientes.index'));
        $response->assertOk();
    }

    // ─────────────────────────────────────────────────────────────
    // CRUD
    // ─────────────────────────────────────────────────────────────

    public function test_admin_puede_crear_cliente(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)
            ->post(route('clientes.store'), $this->datosCliente());

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', [
            'apellidos' => 'GARCIA',
            'nombres'   => 'Carlos',
            'celular'   => '987654321',
        ]);
    }

    public function test_crear_cliente_falla_sin_apellidos(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)
            ->post(route('clientes.store'), $this->datosCliente(['apellidos' => '']));

        $response->assertSessionHasErrors('apellidos');
    }

    public function test_crear_cliente_falla_sin_celular(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)
            ->post(route('clientes.store'), $this->datosCliente(['celular' => '']));

        $response->assertSessionHasErrors('celular');
    }

    public function test_dni_debe_ser_unico(): void
    {
        $admin = $this->usuarioConRol('Administrador');
        Cliente::factory()->create(['dni' => '99999999']);

        $response = $this->actingAs($admin)
            ->post(route('clientes.store'), $this->datosCliente([
                'dni'              => '99999999',
                'numero_documento' => '99999998',
            ]));

        $response->assertSessionHasErrors('dni');
    }

    public function test_admin_puede_editar_cliente(): void
    {
        $admin   = $this->usuarioConRol('Administrador');
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($admin)
            ->put(route('clientes.update', $cliente), $this->datosCliente([
                'dni'              => $cliente->dni,
                'numero_documento' => $cliente->numero_documento,
                'apellidos'        => 'PEREZ',
                'nombres'          => 'Juan',
            ]));

        $response->assertRedirect(route('clientes.show', $cliente));
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'apellidos' => 'PEREZ']);
    }

    public function test_admin_puede_archivar_cliente(): void
    {
        $admin   = $this->usuarioConRol('Administrador');
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    public function test_vendedor_no_puede_archivar_cliente(): void
    {
        $vendedor = $this->usuarioConRol('Vendedor');
        $cliente  = Cliente::factory()->create();

        $response = $this->actingAs($vendedor)
            ->delete(route('clientes.destroy', $cliente));

        $response->assertForbidden();
        $this->assertNotSoftDeleted('clientes', ['id' => $cliente->id]);
    }

    // ─────────────────────────────────────────────────────────────
    // Etiquetas y segmentación
    // ─────────────────────────────────────────────────────────────

    public function test_cliente_se_guarda_con_etiquetas(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)->post(route('clientes.store'), $this->datosCliente([
            'etiquetas' => ['Mamá', 'Mujer'],
        ]));

        $cliente = Cliente::where('apellidos', 'GARCIA')->first();
        $this->assertNotNull($cliente);
        $this->assertContains('Mamá', $cliente->etiquetas);
        $this->assertContains('Mujer', $cliente->etiquetas);
    }

    public function test_etiqueta_invalida_es_rechazada(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)->post(route('clientes.store'), $this->datosCliente([
            'etiquetas' => ['EtiquetaInventada'],
        ]));

        $response->assertSessionHasErrors('etiquetas.0');
    }

    public function test_filtrar_clientes_por_etiqueta(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        Cliente::factory()->mama()->create(['apellidos' => 'MAMA_CLIENTE']);
        Cliente::factory()->papa()->create(['apellidos' => 'PAPA_CLIENTE']);
        Cliente::factory()->create(['apellidos' => 'SIN_ETIQUETA', 'etiquetas' => []]);

        $response = $this->actingAs($admin)
            ->get(route('clientes.index', ['etiqueta' => 'Mamá']));

        $response->assertOk();
        $response->assertSee('MAMA_CLIENTE');
        $response->assertDontSee('PAPA_CLIENTE');
        $response->assertDontSee('SIN_ETIQUETA');
    }

    public function test_scope_con_etiqueta_funciona(): void
    {
        Cliente::factory()->mama()->count(3)->create();
        Cliente::factory()->papa()->count(2)->create();

        $mamas = Cliente::conEtiqueta('Mamá')->count();
        $papas = Cliente::conEtiqueta('Papá')->count();

        $this->assertEquals(3, $mamas);
        $this->assertEquals(2, $papas);
    }

    public function test_scope_para_whatsapp_excluye_sin_celular(): void
    {
        Cliente::factory()->mama()->create(['celular' => '987000001', 'acepta_whatsapp' => true]);
        Cliente::factory()->mama()->sinWhatsapp()->create(['celular' => '987000002']);
        Cliente::factory()->mama()->create(['celular' => null, 'acepta_whatsapp' => true]);

        $count = Cliente::conEtiqueta('Mamá')->paraWhatsapp()->count();
        $this->assertEquals(1, $count);
    }

    public function test_metodo_tiene_etiqueta(): void
    {
        $cliente = Cliente::factory()->mama()->make();

        $this->assertTrue($cliente->tieneEtiqueta('Mamá'));
        $this->assertTrue($cliente->tieneEtiqueta('Mujer'));
        $this->assertFalse($cliente->tieneEtiqueta('Papá'));
    }

    // ─────────────────────────────────────────────────────────────
    // Lista de difusión
    // ─────────────────────────────────────────────────────────────

    public function test_vista_difusion_requiere_autenticacion(): void
    {
        $response = $this->get(route('clientes.difusion'));
        $response->assertRedirect(route('login'));
    }

    public function test_vista_difusion_carga_correctamente(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)->get(route('clientes.difusion'));

        $response->assertOk();
        $response->assertViewIs('clientes.difusion');
        $response->assertViewHas('etiquetasDisponibles');
    }

    public function test_difusion_filtra_por_etiqueta(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        Cliente::factory()->mama()->count(4)->create(['acepta_whatsapp' => true]);
        Cliente::factory()->papa()->count(2)->create(['acepta_whatsapp' => true]);

        $response = $this->actingAs($admin)
            ->get(route('clientes.difusion', ['etiqueta' => 'Mamá']));

        $response->assertOk();
        $response->assertViewHas('total', 4);
    }

    public function test_exportar_whatsapp_requiere_etiqueta_valida(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $response = $this->actingAs($admin)
            ->get(route('clientes.difusion.exportar', ['etiqueta' => 'InvalidaXYZ']));

        $response->assertSessionHasErrors('etiqueta');
    }

    public function test_exportar_whatsapp_descarga_excel(): void
    {
        $admin = $this->usuarioConRol('Administrador');
        Cliente::factory()->mama()->count(2)->create(['acepta_whatsapp' => true]);

        $response = $this->actingAs($admin)
            ->get(route('clientes.difusion.exportar', ['etiqueta' => 'Mamá']));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }
}
