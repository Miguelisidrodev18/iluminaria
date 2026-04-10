<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SerieComprobante;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de emisión de comprobantes: Factura, Boleta y Guía de Remisión.
 *
 * Cubren:
 * 1. Modelo SerieComprobante — correlativo y numeración
 * 2. Modelo Venta — accessor numero_documento
 * 3. Validación del tipo_comprobante en VentaController
 * 4. Vista de impresión / PDF del comprobante
 */
class ComprobanteTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Helpers
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

    private function crearSucursal(): Sucursal
    {
        return Sucursal::create([
            'codigo'    => 'T001',
            'nombre'    => 'Tienda Principal',
            'direccion' => 'Av. Test 123',
            'estado'    => 'activo',
        ]);
    }

    private function crearSerie(array $override = []): SerieComprobante
    {
        $sucursal = $this->crearSucursal();

        return SerieComprobante::create(array_merge([
            'sucursal_id'       => $sucursal->id,
            'tipo_comprobante'  => '01',
            'tipo_nombre'       => 'Factura Electrónica',
            'serie'             => 'FA01',
            'correlativo_actual'=> 1,
            'formato_impresion' => 'A4',
            'activo'            => true,
        ], $override));
    }

    // ─────────────────────────────────────────────────────────────
    // SerieComprobante — Modelo
    // ─────────────────────────────────────────────────────────────

    public function test_correlativo_inicial_es_1(): void
    {
        $serie = $this->crearSerie(['correlativo_actual' => 1]);
        $this->assertEquals(1, $serie->correlativo_actual);
    }

    public function test_siguiente_correlativo_incrementa_en_1(): void
    {
        $serie = $this->crearSerie(['correlativo_actual' => 5]);

        $correlativo = $serie->siguienteCorrelativo();

        $this->assertEquals(5, $correlativo);
        $this->assertEquals(6, $serie->fresh()->correlativo_actual);
    }

    public function test_numero_documento_formatea_con_8_ceros(): void
    {
        $serie = $this->crearSerie([
            'serie'             => 'FA01',
            'correlativo_actual'=> 1,
        ]);

        $numero = $serie->numeroDocumento(1);

        $this->assertEquals('FA01-00000001', $numero);
    }

    public function test_numero_documento_formatea_correlativo_grande(): void
    {
        $serie = $this->crearSerie(['serie' => 'BA01', 'tipo_comprobante' => '03']);

        $numero = $serie->numeroDocumento(12345);

        $this->assertEquals('BA01-00012345', $numero);
    }

    public function test_serie_boleta_tiene_prefijo_B(): void
    {
        $serie = $this->crearSerie([
            'tipo_comprobante' => '03',
            'serie'            => 'BA01',
        ]);

        $this->assertStringStartsWith('B', $serie->serie);
    }

    public function test_serie_guia_tiene_prefijo_T(): void
    {
        $serie = $this->crearSerie([
            'tipo_comprobante' => '09',
            'serie'            => 'T001',
            'tipo_nombre'      => 'Guía de Remisión Remitente',
        ]);

        $this->assertStringStartsWith('T', $serie->serie);
    }

    public function test_tipos_estandar_incluyen_factura_boleta_y_guia(): void
    {
        $tipos = array_column(SerieComprobante::TIPOS_ESTANDAR, 'tipo_comprobante');

        $this->assertContains('01', $tipos, 'Debe incluir Factura (01)');
        $this->assertContains('03', $tipos, 'Debe incluir Boleta (03)');
        $this->assertContains('09', $tipos, 'Debe incluir Guía de Remisión (09)');
    }

    // ─────────────────────────────────────────────────────────────
    // Venta — Accessor numero_documento
    // ─────────────────────────────────────────────────────────────

    public function test_numero_documento_venta_es_null_sin_serie(): void
    {
        $venta = new Venta(['correlativo' => 5]);

        $this->assertNull($venta->numero_documento);
    }

    public function test_numero_documento_venta_se_arma_correctamente(): void
    {
        $serie = $this->crearSerie([
            'serie'             => 'FA01',
            'correlativo_actual'=> 10,
        ]);

        $venta                    = new Venta(['correlativo' => 10]);
        $venta->serie_comprobante_id = $serie->id;
        $venta->setRelation('serieComprobante', $serie);

        $this->assertEquals('FA01-00000010', $venta->numero_documento);
    }

    // ─────────────────────────────────────────────────────────────
    // VentaController — Validación tipo_comprobante
    // ─────────────────────────────────────────────────────────────

    public function test_tipo_comprobante_invalido_es_rechazado(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'       => 1,
            'tipo_comprobante' => 'ticket_invalido',
            'detalles'         => [
                ['producto_id' => 1, 'cantidad' => 1, 'precio_unitario' => 10],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo_comprobante');
    }

    public function test_tipo_comprobante_acepta_boleta(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        // Solo verificamos que la validación del campo pasa (no 422 por tipo_comprobante)
        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'       => 999, // almacén no existe, fallará por eso
            'tipo_comprobante' => 'boleta',
            'detalles'         => [],
        ]);

        // El error debe ser por almacen_id o detalles, NO por tipo_comprobante
        $response->assertStatus(422);
        $data = $response->json('errors');
        $this->assertArrayNotHasKey('tipo_comprobante', $data);
    }

    public function test_tipo_comprobante_acepta_factura(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'       => 999,
            'tipo_comprobante' => 'factura',
            'detalles'         => [],
        ]);

        $data = $response->json('errors');
        $this->assertArrayNotHasKey('tipo_comprobante', $data);
    }

    public function test_tipo_comprobante_acepta_cotizacion(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'       => 999,
            'tipo_comprobante' => 'cotizacion',
            'detalles'         => [],
        ]);

        $data = $response->json('errors');
        $this->assertArrayNotHasKey('tipo_comprobante', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // Catálogo de tipos (TIPOS constante)
    // ─────────────────────────────────────────────────────────────

    public function test_catalogo_TIPOS_tiene_nombre_para_cada_codigo(): void
    {
        foreach (SerieComprobante::TIPOS as $codigo => $info) {
            $this->assertArrayHasKey('nombre', $info, "El tipo '{$codigo}' debe tener 'nombre'");
            $this->assertArrayHasKey('prefijo', $info, "El tipo '{$codigo}' debe tener 'prefijo'");
            $this->assertNotEmpty($info['nombre']);
            $this->assertNotEmpty($info['prefijo']);
        }
    }

    public function test_factura_electronica_tiene_codigo_01(): void
    {
        $this->assertArrayHasKey('01', SerieComprobante::TIPOS);
        $this->assertStringContainsString('Factura', SerieComprobante::TIPOS['01']['nombre']);
    }

    public function test_boleta_electronica_tiene_codigo_03(): void
    {
        $this->assertArrayHasKey('03', SerieComprobante::TIPOS);
        $this->assertStringContainsString('Boleta', SerieComprobante::TIPOS['03']['nombre']);
    }

    public function test_guia_remision_tiene_codigo_09(): void
    {
        $this->assertArrayHasKey('09', SerieComprobante::TIPOS);
        $this->assertStringContainsString('Guía', SerieComprobante::TIPOS['09']['nombre']);
    }

    // ─────────────────────────────────────────────────────────────
    // Acceso a rutas de ventas
    // ─────────────────────────────────────────────────────────────

    public function test_invitado_no_puede_acceder_a_ventas(): void
    {
        $response = $this->get(route('ventas.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_tienda_puede_acceder_a_ventas(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->get(route('ventas.index'));
        $response->assertOk();
    }

    public function test_venta_store_requiere_al_menos_un_detalle(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id' => 1,
            'detalles'   => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('detalles');
    }

    public function test_guia_remision_es_campo_opcional(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'   => 999,
            'guia_remision'=> 'T001-00000001',
            'detalles'     => [],
        ]);

        // No debe fallar por guia_remision
        $data = $response->json('errors');
        $this->assertArrayNotHasKey('guia_remision', $data);
    }

    public function test_guia_remision_max_100_caracteres(): void
    {
        $tienda = $this->usuarioConRol('Tienda');

        $response = $this->actingAs($tienda)->postJson(route('ventas.store'), [
            'almacen_id'    => 999,
            'guia_remision' => str_repeat('X', 101),
            'detalles'      => [['producto_id' => 1, 'cantidad' => 1, 'precio_unitario' => 10]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('guia_remision');
    }
}
