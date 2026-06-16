<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui você pode registrar rotas para sua aplicação. Estas rotas são carregadas
| pelo RouteServiceProvider e agrupadas no grupo "web" com middleware padrão.
|
*/

// Redireciona a rota raiz para a página de login
Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Rotas protegidas por autenticação
|--------------------------------------------------------------------------
|
| Estas rotas só estão acessíveis para usuários autenticados.
|
*/
Route::group([
    'namespace' => 'Admin',
    'middleware' => 'auth',
    'prefix' => 'admin'
], function () {
    /**
     * Página Inicial do Sistema
     */
    Route::get('/home', 'HomeController@index')->name('admin.home');

    // Bingos
    Route::resource('bingos', 'BingoController');
    Route::post('bingos/{bingo}/start', 'BingoController@start')->name('bingos.start');
    Route::post('bingos/{bingo}/finish', 'BingoController@finish')->name('bingos.finish');

    // Responsáveis
    Route::resource('responsibles', 'ResponsibleController');

    // Cartelas
    Route::get('cards/generate', 'CardController@generateForm')->name('cards.generate.form');
    Route::post('cards/generate', 'CardController@generate')->name('cards.generate');
    Route::get('cards', 'CardController@index')->name('cards.index');
    Route::get('cards/export', 'CardController@export')->name('cards.export');
    Route::post('cards/{card}/assign', 'CardController@assign')->name('cards.assign');

    // Sorteio
    Route::get('draw/{bingo}', 'DrawController@index')->name('draw.index');

    // Ganhadores
    Route::get('winners', 'WinnerController@index')->name('winners.index');
    Route::post('winners/{winner}/confirm', 'WinnerController@confirm')->name('winners.confirm');

    // Relatórios
    Route::get('reports', 'ReportController@index')->name('reports.index');

    // Configurações
    Route::get('settings', 'SettingsController@index')->name('settings.index');

    /*
    |--------------------------------------------------------------------------
    | Gerenciamento de Usuários
    |--------------------------------------------------------------------------
    |
    | Rotas relacionadas ao gerenciamento de usuários, incluindo registro,
    | edição de perfil e exclusão. Protegidas por middleware 'auth'.
    |
    */
    Route::group(['prefix' => 'users'], function () {
        /**
         * Gestão de Usuaários do Sistema
         */
        Route::get('user', 'UserController@index')->name('user.index');         // Exibir lista de usuários
        Route::get('user/create', 'UserController@create')->name('user.create'); // Formulário de criação de usuário
        Route::post('user/admin', 'UserController@storeAdmin')->name('user.registro.admin');         // Salvar novo usuário
        Route::post('user/app', 'UserController@storeApp')->name('user.registro.app');         // Salvar novo usuário
        Route::get('user/{user}/edit', 'UserController@edit')->name('user.edit'); // Formulário de edição de usuário
        Route::put('user', 'UserController@update')->name('user.update'); // Atualizar usuário existente
        Route::delete('user/{user}', 'UserController@destroy')->name('user.destroy'); // Excluir usuário


        /**
         * Gestão de Perfil Autenticado
         */
        Route::get('profile', ['as' => 'profile.edit', 'uses' => 'ProfileController@edit']);
        Route::put('profile', ['as' => 'profile.update', 'uses' => 'ProfileController@update']);
        Route::put('profile/password', ['as' => 'profile.password', 'uses' => 'ProfileController@password']);

        // Registro de usuário adicional
        // Route::post('registro', 'UserController@registro')->name('user.registro');

        // Exclusão de usuário
        Route::get('delete/{id}', 'UserController@delete')->name('user.delete');
    });
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
|
| Rotas para login, logout, registro e redefinição de senha. Inclui também
| rotas para confirmação de senha e verificação de e-mail.
|
*/
// Login
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registro
// Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
// Route::post('register', 'Auth\RegisterController@register');

// Redefinição de Senha
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// Confirmação de Senha
Route::get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
Route::post('password/confirm', 'Auth\ConfirmPasswordController@confirm');

// Verificação de E-mail
Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

// Tela Pública (sem auth)
Route::get('bingo/{bingo}/tela', 'Admin\PublicScreenController@show')->name('public.screen');
Route::get('bingo/{bingo}/tela/estado', 'Admin\PublicScreenController@state')->name('public.screen.state');
Route::get('bingo/{bingo}/tela/stream', 'Admin\PublicScreenController@stream')->name('public.screen.stream');
