<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    protected $table = 'clientes'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idcli'; // Nombre de la clave primaria
    public $incrementing = true; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'nombrecli',
        'telefonocli',
        'pais',
        'email',
        'password',
        'saldo',
        'codigo_referidor',
        'referido_por',
        'ya_compro',
        'telegram_chat_id',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idcli', 'idcli');
    }
    public function viewClienteUsuario()
    {
        return $this->hasOne(ViewClientesUsuarios::class, 'idcli', 'idcli');
    }
    public function usuarios()
    {
        return $this->hasMany(ViewUsuarioActivo::class, 'idcli', 'idcli');
    }

    /**
     * Buscar cliente por telegram_chat_id
     */
    public static function buscarPorTelegram(int $chatId): ?self
    {
        return static::where('telegram_chat_id', $chatId)->first();
    }

    /**
     * Vincular telegram_chat_id a este cliente
     */
    public function vincularTelegram(int $chatId): bool
    {
        return $this->update(['telegram_chat_id' => $chatId]);
    }

    /**
     * Verificar si el cliente tiene Telegram vinculado
     */
    public function tieneTelegramVinculado(): bool
    {
        return !is_null($this->telegram_chat_id);
    }
}
