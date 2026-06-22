<?php

/**
 * routes_patch.php — muestra los bloques a agregar en web.php
 *
 * NO reemplaza tu web.php completo. El script 06_apply_fase4.ps1
 * los inyecta automáticamente en las posiciones correctas.
 *
 * RUTAS NUEVAS A AGREGAR:
 *
 * 1. Rutas públicas (sin autenticación, sin tenant):
 *
 *   Route::get('/register', [TenantRegistrationController::class, 'create'])->name('register.tenant.form');
 *   Route::post('/register', [TenantRegistrationController::class, 'store'])->name('register.tenant');
 *
 * 2. El middleware ResolveTenant se registra en bootstrap/app.php
 *    como middleware global (se aplica a todas las rutas).
 *
 * 3. Ruta de suspensión de suscripción:
 *   Route::get('/suspendido', fn() => view('billing.suspendido'))->name('billing.suspendido');
 */
