<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    if ($request->has('tenant_id')) {
        $tenant = Tenant::find($request->query('tenant_id'));
        if ($tenant && $tenant->status !== 'suspended') {
            session(['tenant_id' => $tenant->id]);
        }
    }

    if (!session()->has('tenant_id')) {
        return redirect('/login');
    }
    return view('welcome');
});

Route::get('/saas-admin', function () {
    if (!session()->has('admin_logged_in')) {
        return redirect('/saas-admin/login');
    }
    return view('admin');
});

Route::get('/login', function () {
    if (session()->has('tenant_id')) {
        return redirect('/');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $tenantId = $request->input('tenant_id');
    $username = $request->input('username');
    $password = $request->input('password');

    $tenant = Tenant::find($tenantId);
    
    if (!$tenant) {
        return back()->withErrors(['tenant_id' => 'Invalid Tenant ID. Check spelling or create the tenant in the SaaS Admin Console first.'])->withInput();
    }

    if ($tenant->status === 'suspended') {
        return back()->withErrors(['tenant_id' => 'This SaaS tenant context has been suspended by administration.'])->withInput();
    }

    // Authenticate operator user under this tenant scope
    $user = \App\Models\TenantUser::where('tenant_id', $tenantId)
        ->where('username', $username)
        ->first();

    if (!$user || !\Hash::check($password, $user->password)) {
        return back()->withErrors(['password' => 'Invalid Operator Username or Password for this Tenant account.'])->withInput();
    }
    
    session([
        'tenant_id' => $tenantId,
        'user_id' => $user->id,
        'username' => $user->username,
        'user_name' => $user->name,
        'user_role' => $user->role
    ]);

    return redirect('/');
});

Route::get('/logout', function () {
    session()->forget(['tenant_id', 'user_id', 'username', 'user_name', 'user_role']);
    return redirect('/login');
});

// SaaS Admin login routes
Route::get('/saas-admin/login', function () {
    if (session()->has('admin_logged_in')) {
        return redirect('/saas-admin');
    }
    return view('auth.admin-login');
});

Route::post('/saas-admin/login', function (Request $request) {
    $username = $request->input('username');
    $password = $request->input('password');

    if ($username === 'admin' && $password === 'admin123') {
        session(['admin_logged_in' => true]);
        return redirect('/saas-admin');
    }

    return back()->withErrors(['username' => 'Invalid credentials. Hint: use admin / admin123'])->withInput();
});

Route::get('/saas-admin/logout', function () {
    session()->forget('admin_logged_in');
    return redirect('/saas-admin/login');
});
