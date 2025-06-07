<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\RepartidorController;

// Página de inicio
Route::get('/', [PlatoController::class, 'index'])->name('welcome');
Route::post('/guardar-resena', [PlatoController::class, 'guardarResena'])->name('guardarResena');

// Registro y autenticación
Route::get('/register', [RegisteredUserController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroyAdmin'])->name('admin.logout')->middleware('auth');

// Selector de acceso por rol
Route::get('/acceso/{rol}', function ($rol) {
    if (!in_array($rol, ['cliente', 'chef', 'admin', 'repartidor'])) abort(404);
    return view('select-auth', compact('rol'));
});

// Rutas protegidas para usuarios autenticados
Route::middleware(['auth'])->group(function () {

    // Cliente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('dashboard');
        Route::post('/pedido', [ClienteController::class, 'storePedido'])->name('pedido.store');

        Route::prefix('carrito')->name('carrito.')->group(function () {
            Route::get('/', [ClienteController::class, 'mostrarCarrito'])->name('index');
            Route::post('/agregar', [ClienteController::class, 'agregarAlCarrito'])->name('agregar');
            Route::post('/confirmar', [ClienteController::class, 'confirmarPedido'])->name('confirmar');
            Route::post('/actualizar', [ClienteController::class, 'actualizarCantidadCarrito'])->name('actualizar');
            Route::delete('/{id}', [ClienteController::class, 'eliminarPlatoCarrito'])->name('eliminar');
        });

        Route::get('/reseñas', [ClienteController::class, 'mostrarResenas'])->name('reseñas');
    });

    // Chef
    Route::prefix('chef')->name('chef.')->group(function () {
        Route::get('/', [ChefController::class, 'index'])->name('index');
        Route::get('/menu', fn() => view('menu'))->name('menu');
        Route::get('/pedidos', fn() => view('pedidos'))->name('pedidos');
        Route::get('/inventario', [ChefController::class, 'inventario'])->name('inventario');

        Route::get('/plato/create', [ChefController::class, 'createPlato'])->name('plato.create');
        Route::post('/plato', [ChefController::class, 'storePlato'])->name('plato.store');
        Route::get('/plato/{id}/edit', [ChefController::class, 'editPlato'])->name('plato.edit');
        Route::put('/plato/{id}', [ChefController::class, 'updatePlato'])->name('plato.update');
        Route::delete('/plato/{id}', [ChefController::class, 'destroyPlato'])->name('plato.destroy');

        Route::get('/mandar-pedido', [ChefController::class, 'mandarPedido'])->name('mandar-pedido');
        Route::put('/pedido/{id}', [ChefController::class, 'updatePedido'])->name('pedido.update');

        Route::get('/pedido/actualizar-agrupado', fn() => view('chef.actualizar-agrupado'))->name('pedido.actualizar-agrupado.view');
        Route::post('/pedido/actualizar-agrupado', [ChefController::class, 'updatePedidoAgrupado'])->name('pedido.actualizar-agrupado');
    });

    // Repartidor
    Route::prefix('repartidor')->name('repartidor.')->group(function () {
        Route::get('/', [RepartidorController::class, 'dashboard'])->name('dashboard');

        Route::get('/iniciar-sesion', fn() => view('iniciar-sesion-repartidor'))->name('iniciar-sesion');
        Route::get('/aceptar-pedido', fn() => view('aceptar-pedido'))->name('aceptar-pedido');
        Route::get('/recoger-pedido', fn() => view('recoger-pedido'))->name('recoger-pedido');
        Route::get('/actualizar-estado', fn() => view('actualizar-estado'))->name('actualizar-estado');
        Route::get('/notificar-entrega', fn() => view('notificar-entrega'))->name('notificar-entrega');
        Route::get('/resolver-incidencias', fn() => view('resolver-incidencias'))->name('resolver-incidencias');
        Route::put('/pedido/{id}/actualizar-estado', [RepartidorController::class, 'actualizarEstado'])->name('pedido.actualizarEstado');
    });

    // Perfil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/show', [ProfileController::class, 'show'])->name('show');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Rutas para admin (requiere middleware)
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminController::class)->except(['show']);
    Route::get('/chef/{id}', [AdminController::class, 'showChefDetails'])->name('chef.details');

    Route::get('/platos', [PlatoController::class, 'index'])->name('platos.index');
    Route::get('/platos/create', [PlatoController::class, 'create'])->name('platos.create');
    Route::post('/platos', [PlatoController::class, 'store'])->name('platos.store');
    Route::get('/platos/{id}/edit', [PlatoController::class, 'edit'])->name('platos.edit');
    Route::put('/platos/{id}', [PlatoController::class, 'update'])->name('platos.update');
    Route::delete('/platos/{id}', [PlatoController::class, 'destroy'])->name('platos.destroy');
});

// Carga rutas de auth y otras adicionales
require __DIR__.'/auth.php';
require __DIR__.'/web_additional.php';
