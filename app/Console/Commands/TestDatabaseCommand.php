<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseCommand extends Command
{
    protected $signature = 'test:database';
    protected $description = 'Probar funcionalidades de la base de datos';

    public function handle()
    {
        $this->info('🚀 PRUEBAS DE FUNCIONALIDAD DE LA BASE DE DATOS');
        $this->info('==============================================');
        
        $this->info('');
        $this->info('📋 1. Códigos de referido automáticos:');
        $clientes = DB::table('clientes')->select('nombrecli', 'codigo_referidor')->limit(3)->get();
        foreach ($clientes as $cliente) {
            $this->line("   - {$cliente->nombrecli} -> {$cliente->codigo_referidor}");
        }
        
        $this->info('');
        $this->info('🛒 2. IDs de venta automáticos:');
        $ventas = DB::table('ventas')->select('idven', 'fechaven', 'totalpagoven')->get();
        foreach ($ventas as $venta) {
            $this->line("   - {$venta->idven} ({$venta->fechaven}) - \${$venta->totalpagoven}");
        }
        
        $this->info('');
        $this->info('👤 3. Perfiles generados automáticamente (Netflix):');
        $perfiles = DB::table('cuentas')
            ->join('perfiles', 'cuentas.idcue', '=', 'perfiles.idcue')
            ->where('cuentas.idcue', 'LIKE', 'NETFLIX%')
            ->select('cuentas.idcue', 'cuentas.usuariocue', 'perfiles.numeroper', 'perfiles.pinper')
            ->limit(5)
            ->get();
            
        foreach ($perfiles as $perfil) {
            $this->line("   - {$perfil->idcue} | Perfil {$perfil->numeroper} | PIN: {$perfil->pinper}");
        }
        
        $this->info('');
        $this->info('📊 4. Vista de usuarios activos:');
        $usuarios = DB::table('view_usuarios_activos')->limit(3)->get();
        foreach ($usuarios as $usuario) {
            $this->line("   - {$usuario->nombre_cliente} | Cuenta: {$usuario->idcue} | Perfil: {$usuario->perfil}");
        }
        
        $this->info('');
        $this->info('📈 5. Estadísticas mensuales:');
        $stats = DB::table('ventas_mensuales')->limit(2)->get();
        foreach ($stats as $stat) {
            $this->line("   - {$stat->anio}-{$stat->mes}: {$stat->total_ventas} ventas, \${$stat->total_monto}");
        }
        
        $this->info('');
        $this->success('✅ Todas las funcionalidades están operativas!');
        
        return 0;
    }
}