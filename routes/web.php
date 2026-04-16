<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ===================== AUTH =====================
use App\Http\Controllers\Auth\MasterPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\DirectPasswordResetController;
use App\Http\Controllers\ProfileController;

// ===================== CORE =====================
use App\Http\Controllers\DashboardController;

// ===================== ADMIN =====================
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\AdminCajaController;
use App\Http\Controllers\Admin\AtributoController;

// ===================== LUMINARIAS =====================
use App\Http\Controllers\Luminaria\TipoProyectoController;
use App\Http\Controllers\Luminaria\EspacioProyectoController;
use App\Http\Controllers\Luminaria\ProductoEspecificacionController;
use App\Http\Controllers\Luminaria\ProductoDimensionController;
use App\Http\Controllers\Luminaria\ProductoMaterialController;
use App\Http\Controllers\Luminaria\ProductoClasificacionController;

// ===================== INVENTARIO =====================
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\AlmacenController;

// ===================== NUEVOS MÓDULOS =====================
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProveedorImportadorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\TrasladoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\PrecioController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CuentaPorPagarController;
use App\Http\Controllers\ReporteVentasController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\GuiaRemisionController;

// ===================== MIDDLEWARE =====================
use App\Http\Middleware\VerifyMasterPassword;

Route::get('/', function () {
    return redirect()->route('login');
});
/*
|--------------------------------------------------------------------------
| RUTA PRINCIPAL
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $role = auth()->user()->role->nombre ?? null;
        return match ($role) {
        'Administrador' => redirect()->route('admin.dashboard'),
        'Almacenero'    => redirect()->route('almacenero.dashboard'),
        'Vendedor'      => redirect()->route('vendedor.dashboard'),
        'Tienda'        => redirect()->route('tienda.dashboard'),
        default         => redirect()->route('login'),
    };
})->name('dashboard')->middleware('auth');

/*
|--------------------------------------------------------------------------
| CONTRASEÑA MAESTRA
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/master-password', [MasterPasswordController::class, 'show'])->name('master-password.show');
    Route::post('/master-password', [MasterPasswordController::class, 'verify'])->name('master-password.verify');
});

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::middleware(VerifyMasterPassword::class)->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::get('/forgot-password', [DirectPasswordResetController::class, 'show'])->name('password.request');
        Route::post('/forgot-password', [DirectPasswordResetController::class, 'update'])->name('password.update-direct');
    });
});

// ========================================
// MÓDULO DE USUARIOS
// ========================================
Route::middleware(['auth', 'role:Administrador'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
    Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
    Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SEGURIDAD — Verificación de contraseña (AJAX, usado por modal)
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/verify-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['password' => 'required|string']);
        $valid = \Illuminate\Support\Facades\Hash::check(
            $request->password,
            auth()->user()->password
        );
        return response()->json(['valid' => $valid]);
    })->name('auth.verify-password');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARDS POR ROL
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // ── Atributos del catálogo dinámico ────────────────────────────────────
        Route::get('/atributos', [AtributoController::class, 'index'])->name('atributos.index');
        Route::get('/atributos/create', [AtributoController::class, 'create'])->name('atributos.create');
        Route::post('/atributos', [AtributoController::class, 'store'])->name('atributos.store');
        Route::get('/atributos/{atributo}/edit', [AtributoController::class, 'edit'])->name('atributos.edit');
        Route::put('/atributos/{atributo}', [AtributoController::class, 'update'])->name('atributos.update');
        Route::delete('/atributos/{atributo}', [AtributoController::class, 'destroy'])->name('atributos.destroy');
        Route::post('/atributos/{atributo}/valores', [AtributoController::class, 'storeValor'])->name('atributos.valores.store');
    });

    Route::middleware('role:Vendedor')->prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'vendedor'])->name('dashboard');
    });

    Route::middleware('role:Almacenero')->prefix('almacenero')->name('almacenero.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'almacenero'])->name('dashboard');
    });

    Route::middleware('role:Tienda')->prefix('tienda')->name('tienda.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'tienda'])->name('dashboard');
    });

    Route::middleware('role:Proveedor')->prefix('proveedor')->name('proveedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'proveedor'])->name('dashboard');
    });

    // ========================================
    // RUTAS DE PERFIL
    // ========================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ========================================
    // MÓDULO DE INVENTARIO
    // ========================================
    Route::prefix('inventario')->name('inventario.')->group(function () {
        
        // CATEGORÍAS (= Tipos de Producto)
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::get('/categorias/{tipoProducto}', [CategoriaController::class, 'show'])->name('categorias.show');

        // PRODUCTOS
            Route::middleware('role:Administrador,Almacenero')->group(function () {
                Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
                Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
                Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
                Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
                Route::patch('/productos/{producto}/stock-ubicacion', [ProductoController::class, 'actualizarStockUbicacion'])->name('productos.stock-ubicacion');
                Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->middleware('role:Administrador')->name('productos.destroy');
                
                // Gestión de códigos de barras múltiples
                Route::get('/productos/{producto}/codigos-barras', [ProductoController::class, 'codigosBarras'])->name('productos.codigos-barras');
                Route::post('/productos/{producto}/codigos-barras', [ProductoController::class, 'storeCodigoBarras'])->name('productos.codigos-barras.store');
                Route::delete('/productos/codigos-barras/{codigoBarras}', [ProductoController::class, 'destroyCodigoBarras'])->name('productos.codigos-barras.destroy');
                Route::post('/productos/codigos-barras/{codigoBarras}/principal', [ProductoController::class, 'setPrincipalCodigoBarras'])->name('productos.codigos-barras.principal');
                // 🔴 NUEVAS RUTAS PARA GENERAR CÓDIGOS DE BARRAS
                Route::post('/productos/generar-codigo-barras', [ProductoController::class, 'generarCodigoBarras'])->name('productos.generar-codigo-barras');
                Route::get('/productos/validar-codigo-barras', [ProductoController::class, 'validarCodigoBarras'])->name('productos.validar-codigo-barras');
                Route::get('/productos/generar-codigo-kyrios', [ProductoController::class, 'generarCodigoKyrios'])->name('productos.generar-codigo-kyrios');
                // Gestión de proveedores del producto
                Route::get('/productos/{producto}/proveedores', [ProductoController::class, 'proveedores'])->name('productos.proveedores');
                Route::post('/productos/{producto}/proveedores', [ProductoController::class, 'asociarProveedor'])->name('productos.proveedores.asociar');
                Route::delete('/productos/proveedores/{productoProveedor}', [ProductoController::class, 'desasociarProveedor'])->name('productos.proveedores.desasociar');
            });

            Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
            Route::get('/productos/buscar', [ProductoController::class, 'buscarAjax'])->name('productos.buscar-ajax');
            Route::get('/productos/consulta-tienda', [ProductoController::class, 'consultaTienda'])->middleware('role:Tienda')->name('productos.consulta-tienda');
            Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
        // MOVIMIENTOS DE INVENTARIO
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/movimientos', [MovimientoInventarioController::class, 'index'])->name('movimientos.index');
            Route::get('/movimientos/create', [MovimientoInventarioController::class, 'create'])->name('movimientos.create');
            Route::post('/movimientos', [MovimientoInventarioController::class, 'store'])->name('movimientos.store');
            Route::get('/movimientos/{movimiento}', [MovimientoInventarioController::class, 'show'])->name('movimientos.show');
            Route::get('/api/stock-actual', [MovimientoInventarioController::class, 'getStockActual'])->name('movimientos.stock-actual');
            // Stub: sistema IMEI eliminado — devuelve array vacío para compatibilidad con la vista
            Route::get('/api/imeis-disponibles', fn() => response()->json([]))->name('movimientos.imeis-disponibles');
        });

        // ALMACENES
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/almacenes', [AlmacenController::class, 'index'])->name('almacenes.index');
            Route::get('/almacenes/create', [AlmacenController::class, 'create'])->name('almacenes.create');
            Route::post('/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store');
            Route::get('/almacenes/{almacen}', [AlmacenController::class, 'show'])->name('almacenes.show');
            Route::get('/almacenes/{almacen}/edit', [AlmacenController::class, 'edit'])->name('almacenes.edit');
            Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update');
            Route::delete('/almacenes/{almacen}', [AlmacenController::class, 'destroy'])->middleware('role:Administrador')->name('almacenes.destroy');
        });


        // VARIANTES DE PRODUCTOS
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/productos/{producto}/variantes', [ProductoController::class, 'variantes'])->name('productos.variantes');
            Route::post('/productos/{producto}/variantes', [ProductoController::class, 'storeVariante'])->name('productos.variantes.store');
            Route::post('/productos/{producto}/toggle-variantes', [ProductoController::class, 'toggleVariantesMode'])->name('productos.toggle-variantes');
            Route::put('/productos/variantes/{variante}', [ProductoController::class, 'updateVariante'])->name('productos.variantes.update');
            Route::patch('/productos/variantes/{variante}/precio', [ProductoController::class, 'actualizarPrecioVariante'])->name('productos.variantes.precio');
            Route::post('/productos/variantes/{variante}/reactivar', [ProductoController::class, 'reactivarVariante'])->name('productos.variantes.reactivar');
            Route::delete('/productos/variantes/{variante}', [ProductoController::class, 'destroyVariante'])->name('productos.variantes.destroy');
        });

        // FLUJO DE APROBACIÓN
        Route::post('/productos/{producto}/aprobar', [ProductoController::class, 'approve'])->name('productos.aprobar');
        Route::post('/productos/{producto}/rechazar', [ProductoController::class, 'reject'])->name('productos.rechazar');
        Route::post('/productos/{producto}/enviar-aprobacion', [ProductoController::class, 'submitForApproval'])->name('productos.enviar-aprobacion');

        // BOM — COMPONENTES DE PRODUCTOS COMPUESTOS
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::post('/productos/{producto}/componentes', [\App\Http\Controllers\ProductoComponenteController::class, 'store'])->name('productos.componentes.store');
            Route::put('/productos/componentes/{componente}', [\App\Http\Controllers\ProductoComponenteController::class, 'update'])->name('productos.componentes.update');
            Route::delete('/productos/componentes/{componente}', [\App\Http\Controllers\ProductoComponenteController::class, 'destroy'])->name('productos.componentes.destroy');
        });

        // API: componentes de un kit (para venta/cotización)
        Route::get('/productos/{producto}/componentes/api', [\App\Http\Controllers\ProductoComponenteController::class, 'apiComponentes'])->name('productos.componentes.api');

        // IMPORTADOR MASIVO EXCEL (legacy single-sheet)
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/productos/importar/formulario', [\App\Http\Controllers\ImportadorProductosController::class, 'index'])->name('productos.importar');
            Route::post('/productos/importar', [\App\Http\Controllers\ImportadorProductosController::class, 'store'])->name('productos.importar.store');
            Route::get('/productos/importar/plantilla', [\App\Http\Controllers\ImportadorProductosController::class, 'descargarPlantilla'])->name('productos.importar.plantilla');
        });

        // IMPORTADOR MASIVO MULTI-HOJA + APROBACIÓN
        Route::prefix('importacion')->name('importacion.')->middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/',                         [\App\Http\Controllers\ImportacionController::class, 'index'])->name('index');
            Route::post('/',                        [\App\Http\Controllers\ImportacionController::class, 'store'])->name('store');
            Route::get('/plantilla',                [\App\Http\Controllers\ImportacionController::class, 'descargarPlantilla'])->name('plantilla');
            Route::get('/{importacion}/progreso',   [\App\Http\Controllers\ImportacionController::class, 'progreso'])->name('progreso');
            Route::patch('/{importacion}/cancelar', [\App\Http\Controllers\ImportacionController::class, 'cancelar'])->name('cancelar');
            Route::get('/aprobacion',               [\App\Http\Controllers\ImportacionController::class, 'aprobacion'])->name('aprobacion');
            Route::post('/aprobar-lote',            [\App\Http\Controllers\ImportacionController::class, 'aprobarLote'])->name('aprobar-lote');
            Route::delete('/eliminar-lote',         [\App\Http\Controllers\ImportacionController::class, 'eliminarLote'])->name('eliminar-lote');
        });

        // REPORTES DE INVENTARIO (HU-INVENTARIO-06/07/08)
        Route::middleware('role:Administrador')->group(function () {
            Route::get('/reportes/stock-valorizado', [\App\Http\Controllers\Inventario\InventarioReportesController::class, 'stockValorizado'])->name('reportes.stock-valorizado');
            Route::get('/reportes/kardex',           [\App\Http\Controllers\Inventario\InventarioReportesController::class, 'kardex'])->name('reportes.kardex');
            Route::get('/reportes/abc',              [\App\Http\Controllers\Inventario\InventarioReportesController::class, 'analisisAbc'])->name('reportes.abc');
        });

    });

    // ========================================
    // MÓDULO DE PROVEEDORES
    // ========================================
    Route::prefix('proveedores')->name('proveedores.')->middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('create');
        Route::post('/', [ProveedorController::class, 'store'])->name('store');
        // Importación masiva (before /{proveedor} to avoid param capture)
        Route::prefix('importar')->name('importar.')->group(function () {
            Route::get('/', [ProveedorImportadorController::class, 'index'])->name('index');
            Route::get('/plantilla', [ProveedorImportadorController::class, 'descargarPlantilla'])->name('plantilla');
            Route::post('/', [ProveedorImportadorController::class, 'store'])->name('store');
            Route::get('/errores', [ProveedorImportadorController::class, 'descargarErrores'])->name('errores');
        });
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->middleware('role:Administrador')->name('destroy');
        Route::post('/consultar-sunat', [ProveedorController::class, 'consultarSunat'])->name('consultar-sunat');
    });

    // ========================================
    // MÓDULO DE CLIENTES
    // ========================================
    Route::prefix('clientes')->name('clientes.')->middleware('role:Administrador,Vendedor,Tienda')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');
        Route::get('/exportar', [ClienteController::class, 'exportar'])->name('exportar');
        Route::get('/difusion', [ClienteController::class, 'difusion'])->name('difusion');
        Route::get('/difusion/exportar', [ClienteController::class, 'exportarWhatsapp'])->name('difusion.exportar');
        Route::get('/create', [ClienteController::class, 'create'])->name('create');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::post('/consultar-documento', [ClienteController::class, 'consultarDocumento'])->name('consultar-documento');
        Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
        Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit');
        Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update');
        Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->middleware('role:Administrador')->name('destroy');
        Route::post('/{cliente}/visitas', [ClienteController::class, 'storeVisita'])->name('visitas.store');
    });

    // ========================================
    // MÓDULO DE PROYECTOS
    // ========================================
    Route::prefix('proyectos')->name('proyectos.')->middleware('role:Administrador,Vendedor')->group(function () {
        Route::get('/', [ProyectoController::class, 'index'])->name('index');
        Route::post('/', [ProyectoController::class, 'store'])->name('store');
        Route::get('/{proyecto}', [ProyectoController::class, 'show'])->name('show');
        Route::put('/{proyecto}', [ProyectoController::class, 'update'])->name('update');
    });

    // ========================================
    // MÓDULO DE PEDIDOS
    // ========================================
    Route::prefix('pedidos')->name('pedidos.')->middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/', [PedidoController::class, 'index'])->name('index');
        Route::get('/create', [PedidoController::class, 'create'])->name('create');
        Route::post('/', [PedidoController::class, 'store'])->name('store');
        Route::get('/{pedido}', [PedidoController::class, 'show'])->name('show');
        Route::patch('/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('cambiar-estado');
    });

    Route::middleware('role:Proveedor')->group(function () {
        Route::get('/proveedor/pedidos', [PedidoController::class, 'pedidosProveedor'])->name('proveedor.pedidos');
    });

    // ========================================
    // MÓDULO DE COMPRAS
    // ========================================
    Route::prefix('compras')->name('compras.')->middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/', [CompraController::class, 'index'])->name('index');
        Route::get('/create', [CompraController::class, 'create'])->name('create');
        Route::post('/', [CompraController::class, 'store'])->name('store');
        Route::get('buscar-productos', [CompraController::class, 'buscarProductos'])->name('buscar-productos');
        Route::get('producto/{id}', [CompraController::class, 'getProductoDetalle'])->name('producto-detalle');
        Route::post('crear-producto-rapido', [CompraController::class, 'crearProductoRapido'])->name('crear-producto-rapido');
        Route::get('tipo-cambio', [CompraController::class, 'tipoCambio'])->name('tipo-cambio');
        Route::get('/{compra}', [CompraController::class, 'show'])->name('show');
        Route::get('/{compra}/edit', [CompraController::class, 'edit'])->name('edit');
        Route::put('/{compra}', [CompraController::class, 'update'])->name('update');
        Route::post('/{compra}/anular', [CompraController::class, 'anular'])->name('anular');

    });
   // ========================================
    // MÓDULO DE CUENTAS POR PAGAR
    // ========================================
    Route::prefix('cuentas-por-pagar')->name('cuentas-por-pagar.')->middleware('auth')->group(function () {
        Route::get('/', [App\Http\Controllers\CuentaPorPagarController::class, 'index'])->name('index');

        // Rutas de cuotas (antes de /{cuenta} para evitar conflicto)
        Route::post('/cuotas/{cuota}/pagar', [App\Http\Controllers\CuentaPorPagarController::class, 'pagarCuota'])->name('cuotas.pagar');

        Route::get('/{cuenta}', [App\Http\Controllers\CuentaPorPagarController::class, 'show'])->name('show');
        Route::post('/{cuenta}/registrar-pago', [App\Http\Controllers\CuentaPorPagarController::class, 'registrarPago'])->name('registrar-pago');
        Route::post('/{cuenta}/generar-cuotas', [App\Http\Controllers\CuentaPorPagarController::class, 'generarCuotas'])->name('generar-cuotas');
        Route::get('/{cuenta}/programar-pago', [App\Http\Controllers\CuentaPorPagarController::class, 'programarPago'])->name('programar-pago');
        Route::post('/{cuenta}/programar-pago', [App\Http\Controllers\CuentaPorPagarController::class, 'guardarProgramacion'])->name('guardar-programacion');
    });

    // También puedes agregar una ruta para el dashboard financiero
    Route::get('/finanzas', [App\Http\Controllers\CuentaPorPagarController::class, 'dashboard'])->middleware('role:Administrador,Almacenero')->name('finanzas.dashboard');
    // ========================================
    // MÓDULO DE VENTAS
    // ========================================
    Route::prefix('ventas')->name('ventas.')->middleware('role:Administrador,Vendedor,Tienda')->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::get('/create', [VentaController::class, 'create'])->name('create');
        Route::post('/', [VentaController::class, 'store'])->name('store');
        Route::get('/cotizaciones', [VentaController::class, 'cotizaciones'])->name('cotizaciones');
        Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
        Route::get('/{venta}/pdf', [VentaController::class, 'pdf'])->name('pdf');
        Route::post('/{venta}/confirmar-pago', [VentaController::class, 'confirmarPago'])->middleware('role:Administrador,Tienda')->name('confirmar-pago');
        Route::post('/{venta}/convertir', [VentaController::class, 'convertir'])->middleware('role:Administrador,Tienda')->name('convertir');
    });
    // ========================================
    // MÓDULO DE GUÍAS DE REMISIÓN ELECTRÓNICAS
    // ========================================
    Route::prefix('guias-remision')->name('guias-remision.')->middleware('role:Administrador,Vendedor,Tienda')->group(function () {
        Route::get('/',                                [GuiaRemisionController::class, 'index'])->name('index');
        Route::get('/create',                          [GuiaRemisionController::class, 'create'])->name('create');
        Route::post('/',                               [GuiaRemisionController::class, 'store'])->name('store');
        Route::get('/{guiaRemision}',                  [GuiaRemisionController::class, 'show'])->name('show');
        Route::get('/{guiaRemision}/edit',             [GuiaRemisionController::class, 'edit'])->name('edit');
        Route::put('/{guiaRemision}',                  [GuiaRemisionController::class, 'update'])->name('update');
        Route::delete('/{guiaRemision}',               [GuiaRemisionController::class, 'destroy'])->name('destroy');
        Route::post('/{guiaRemision}/anular',          [GuiaRemisionController::class, 'anular'])->name('anular');
        Route::post('/{guiaRemision}/enviar-sunat',    [GuiaRemisionController::class, 'enviarSunat'])->name('enviar-sunat');
        Route::get('/{guiaRemision}/pdf',              [GuiaRemisionController::class, 'pdf'])->name('pdf');
    });

    // ========================================
    // MÓDULO DE REPORTES DE VENTAS
    // ========================================
    Route::middleware('role:Administrador')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas',     [ReporteVentasController::class, 'index'])->name('ventas');
        Route::get('/ventas/csv', [ReporteVentasController::class, 'exportCsv'])->name('ventas.csv');
        Route::get('/ventas/pdf', [ReporteVentasController::class, 'exportPdf'])->name('ventas.pdf');
    });

    // ========================================
    // MÓDULO DE TIENDA (Inventario entre tiendas)
    // ========================================
    Route::middleware(['auth', 'role:Tienda'])->prefix('tienda')->name('tienda.')->group(function () {
        Route::get('/inventario', [TiendaController::class, 'inventario'])->name('inventario.ver');
        Route::get('/solicitudes', [TiendaController::class, 'solicitudes'])->name('inventario.solicitudes');
        Route::post('/solicitar-traslado', [TiendaController::class, 'solicitarTraslado'])->name('inventario.solicitar-traslado');
        Route::post('/solicitudes/{traslado}/cancelar', [TiendaController::class, 'cancelarSolicitud'])->name('inventario.cancelar-solicitud');
        Route::get('/producto/{producto}', [TiendaController::class, 'verProducto'])->name('producto.ver');
        Route::get('/get-stock', [TiendaController::class, 'getStockProducto'])->name('get-stock');
    });
    // ========================================
    // MÓDULO DE TRASLADOS
    // ========================================
    Route::prefix('traslados')->name('traslados.')->middleware('auth')->group(function () {
        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/', [TrasladoController::class, 'index'])->name('index');
            Route::get('/stock', [TrasladoController::class, 'stock'])->name('stock');
            Route::get('/create', [TrasladoController::class, 'create'])->name('create');
            Route::post('/', [TrasladoController::class, 'store'])->name('store');
        });

        Route::middleware('role:Administrador,Almacenero,Tienda')->group(function () {
            Route::get('/pendientes', [TrasladoController::class, 'pendientes'])->name('pendientes');
            Route::post('/{traslado}/confirmar', [TrasladoController::class, 'confirmar'])->name('confirmar');
        });

        Route::middleware('role:Administrador,Almacenero')->group(function () {
            Route::get('/{traslado}', [TrasladoController::class, 'show'])->name('show');
        });
    });

    // ========================================
    // MÓDULO DE CAJA
    // ========================================
    Route::prefix('caja')->name('caja.')->middleware('role:Administrador,Tienda')->group(function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::get('/abrir', [CajaController::class, 'abrir'])->name('abrir');
        Route::post('/abrir', [CajaController::class, 'store'])->name('store');
        Route::get('/actual', [CajaController::class, 'actual'])->name('actual');
        Route::post('/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
        Route::post('/ingreso', [CajaController::class, 'registrarIngreso'])->name('ingreso');
        Route::post('/gasto', [CajaController::class, 'registrarGasto'])->name('gasto');
        Route::get('/movimientos', [CajaController::class, 'movimientos'])->name('movimientos');
        Route::get('/{caja}', [CajaController::class, 'show'])->name('show');
    });

    // ========================================
    // MÓDULO DE CATÁLOGO
    // ========================================
    Route::prefix('catalogo')->name('catalogo.')->middleware('role:Administrador,Almacenero')->group(function () {
        // Creación rápida desde formularios (deben ir ANTES de los resource)
        Route::post('marcas/rapida',  [App\Http\Controllers\Catalogo\MarcaController::class,  'storeRapida'])->name('marcas.rapida');
        Route::post('modelos/rapida', [App\Http\Controllers\Catalogo\ModeloController::class, 'storeRapida'])->name('modelos.rapida');
        Route::post('colores/rapida', [App\Http\Controllers\Catalogo\ColorController::class,  'storeRapida'])->name('colores.rapida');
        Route::post('unidades/rapida', [App\Http\Controllers\Catalogo\UnidadMedidaController::class,  'storeRapida'])->name('unidades.rapida');

        Route::resource('colores', App\Http\Controllers\Catalogo\ColorController::class)->parameters(['colores' => 'color']);
        Route::resource('motivos', App\Http\Controllers\Catalogo\MotivoMovimientoController::class)->parameters(['motivos' => 'motivo']);
        Route::resource('unidades', App\Http\Controllers\Catalogo\UnidadMedidaController::class)->parameters(['unidades' => 'unidade']);
        Route::resource('marcas', App\Http\Controllers\Catalogo\MarcaController::class)->parameters(['marcas' => 'marca']);
        Route::resource('modelos', App\Http\Controllers\Catalogo\ModeloController::class)->parameters(['modelos' => 'modelo']);
        Route::get('modelos-por-marca/{marcaId}', [App\Http\Controllers\Catalogo\ModeloController::class, 'getModelosPorMarca'])->name('modelos.por-marca');
        Route::get('marcas-por-categoria/{categoriaId}', [App\Http\Controllers\Catalogo\MarcaController::class, 'getMarcasPorCategoria'])->name('marcas.por-categoria');
        Route::resource('ubicaciones', App\Http\Controllers\Catalogo\UbicacionController::class)->parameters(['ubicaciones' => 'ubicacion'])->only(['index','store','update','destroy']);
    });

    // ========================================
    // MÓDULO ADMINISTRACIÓN (solo Administrador)
    // ========================================
    Route::middleware('role:Administrador')->prefix('admin')->name('admin.')->group(function () {
        // Empresa (singleton)
        Route::get('/empresa', [EmpresaController::class, 'edit'])->name('empresa.edit');
        Route::put('/empresa', [EmpresaController::class, 'update'])->name('empresa.update');
        Route::get('/consultar-ruc/{ruc}', [EmpresaController::class, 'consultarRuc'])->name('empresa.consultar-ruc');
        Route::post('/empresa/test-api', [EmpresaController::class, 'testApi'])->name('empresa.test-api');

        // Sucursales
        Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
        Route::get('/sucursales/create', [SucursalController::class, 'create'])->name('sucursales.create');
        Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
        Route::get('/sucursales/{sucursal}/edit', [SucursalController::class, 'edit'])->name('sucursales.edit');
        Route::put('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
        Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
        Route::get('/sucursales/{sucursal}/comprobantes', [SucursalController::class, 'comprobantes'])->name('sucursales.comprobantes');
        Route::post('/sucursales/{sucursal}/generar-series', [SucursalController::class, 'generarSeries'])->name('sucursales.generar-series');

        // Almacenes de una sucursal
        Route::post('/sucursales/{sucursal}/almacenes', [SucursalController::class, 'storeAlmacen'])->name('sucursales.almacenes.store');
        Route::delete('/sucursales/{sucursal}/almacenes/{almacen}', [SucursalController::class, 'destroyAlmacen'])->name('sucursales.almacenes.destroy');

        // Series de comprobantes de una sucursal
        Route::put('/sucursales/{sucursal}/series/{serie}', [SucursalController::class, 'updateSerie'])->name('sucursales.series.update');
        Route::post('/sucursales/{sucursal}/series', [SucursalController::class, 'storeSerie'])->name('sucursales.series.store');

        // Pagos digitales de una sucursal
        Route::put('/sucursales/{sucursal}/pagos', [SucursalController::class, 'updatePagos'])->name('sucursales.pagos.update');

        // ── Admin Caja (Supervisión) ──────────────────────────────────────────
        Route::get('/cajas/dashboard', [AdminCajaController::class, 'dashboard'])->name('cajas.dashboard');
        Route::get('/cajas/alertas', [AdminCajaController::class, 'alertas'])->name('cajas.alertas');
        Route::get('/cajas/reportes', [AdminCajaController::class, 'reportes'])->name('cajas.reportes');
        Route::get('/cajas/apertura-remota', [AdminCajaController::class, 'aperturaRemota'])->name('cajas.apertura-remota');
        Route::post('/cajas/apertura-remota', [AdminCajaController::class, 'storeAperturaRemota'])->name('cajas.apertura-remota.store');
        Route::get('/cajas', [AdminCajaController::class, 'index'])->name('cajas.index');
        Route::get('/cajas/{caja}', [AdminCajaController::class, 'show'])->name('cajas.show');
        Route::post('/cajas/{caja}/forzar-cierre', [AdminCajaController::class, 'forzarCierre'])->name('cajas.forzar-cierre');
        Route::post('/cajas/{caja}/ajustar-diferencia', [AdminCajaController::class, 'ajustarDiferencia'])->name('cajas.ajustar-diferencia');
    });

    // ========================================
    // MÓDULO DE PRECIOS
    // ========================================
    Route::prefix('precios')->name('precios.')->middleware('role:Administrador,Almacenero')->group(function () {
        Route::get('/', [PrecioController::class, 'index'])->name('index');
        Route::get('/proveedores/buscar', [PrecioController::class, 'buscarProveedores'])->name('proveedores.buscar');
        Route::get('/producto/{producto}/ultimo-precio-compra', [PrecioController::class, 'ultimoPrecioCompra'])->name('ultimo-precio-compra');
        Route::get('/producto/{producto}', [PrecioController::class, 'show'])->name('show');
        Route::post('/producto/{producto}', [PrecioController::class, 'store'])->name('store');
        Route::post('/producto/{producto}/calcular', [PrecioController::class, 'calcular'])->name('calcular');
        Route::get('/producto/{producto}/precio/{precio}/edit', [PrecioController::class, 'edit'])->name('edit');
        Route::put('/producto/{producto}/precio/{precio}', [PrecioController::class, 'update'])->name('update');
        Route::get('/producto/{producto}/historial', [PrecioController::class, 'historial'])->name('historial');
    });

    // ========================================
    // MÓDULO LUMINARIAS — CATÁLOGO TÉCNICO
    // ========================================
    Route::prefix('luminarias')->name('luminarias.')->middleware('role:Administrador,Almacenero')->group(function () {

        // Tipos de proyecto
        Route::resource('tipos-proyecto', TipoProyectoController::class)
             ->parameters(['tipos-proyecto' => 'tiposProyecto']);

        // Espacios por tipo de proyecto
        Route::resource('espacios-proyecto', EspacioProyectoController::class)
             ->parameters(['espacios-proyecto' => 'espaciosProyecto']);

        // Fichas técnicas de productos
        Route::resource('producto-especificaciones', ProductoEspecificacionController::class)
             ->parameters(['producto-especificaciones' => 'productoEspecificacion']);

        Route::resource('producto-dimensiones', ProductoDimensionController::class)
             ->parameters(['producto-dimensiones' => 'productoDimension']);

        Route::resource('producto-materiales', ProductoMaterialController::class)
             ->parameters(['producto-materiales' => 'productoMaterial']);

        Route::resource('producto-clasificacion', ProductoClasificacionController::class)
             ->parameters(['producto-clasificacion' => 'productoClasificacion']);
    });

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ========================================
// API INTERNAS (para AJAX del POS y escáner)
// ========================================
Route::prefix('api')->name('api.')->middleware('auth')->group(function () {

    // Búsqueda de productos por código (para escáner)
    Route::get('/productos/buscar', [App\Http\Controllers\Api\ProductoController::class, 'buscarPorCodigo'])
        ->name('productos.buscar');

    // Obtener precios de un producto (con lógica rotativa)
    Route::get('/productos/{producto}/precios', [App\Http\Controllers\Api\ProductoController::class, 'obtenerPrecios'])
        ->name('productos.precios');

    // Obtener stock de producto por almacén
    Route::get('/productos/{producto}/stock', [App\Http\Controllers\Api\ProductoController::class, 'obtenerStock'])
        ->name('productos.stock');

    // Buscar cliente por documento
    Route::get('/clientes/buscar', [App\Http\Controllers\Api\ClienteController::class, 'buscarPorDocumento'])
        ->name('clientes.buscar');

    // Búsqueda dinámica de clientes (autocomplete)
    Route::get('/clientes/buscar-texto', [App\Http\Controllers\Api\ClienteController::class, 'buscarTexto'])
        ->name('clientes.buscar-texto');

    // Obtener tipo de cambio del día
    Route::get('/tipo-cambio', [App\Http\Controllers\Api\TipoCambioController::class, 'obtener'])
        ->name('tipo-cambio');

    // Validar código de barras (para creación de productos)
    Route::get('/validar-codigo-barras', [App\Http\Controllers\Api\ProductoController::class, 'validarCodigoBarras'])
        ->name('validar-codigo-barras');

    // ── VARIANTES ──────────────────────────────────────────────────────────────
    Route::prefix('variantes')->name('variantes.')->group(function () {
        // Variantes de un producto base
        Route::get('/producto/{producto}', [App\Http\Controllers\Api\VarianteController::class, 'porProducto'])
            ->name('por-producto');

        // CRUD de variante individual
        Route::post('/', [App\Http\Controllers\Api\VarianteController::class, 'store'])
            ->name('store');

        Route::get('/{variante}', [App\Http\Controllers\Api\VarianteController::class, 'show'])
            ->name('show');

        Route::put('/{variante}', [App\Http\Controllers\Api\VarianteController::class, 'update'])
            ->name('update');

        Route::delete('/{variante}', [App\Http\Controllers\Api\VarianteController::class, 'destroy'])
            ->name('destroy');

        // Stock e IMEIs de variante
        Route::get('/{variante}/stock', [App\Http\Controllers\Api\VarianteController::class, 'stock'])
            ->name('stock');
    });
});


