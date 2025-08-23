<?php

namespace App\Models;

use App\Models\BusinessCustomerCalculoRegistro;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
  use HasFactory;

  protected $table = 'business';

  protected $fillable = [
    'name',
    'business_type',
    'currency_id',
    'start_date',
    'owner_id',
    'centro_costo_calculo_registro_id',
    'emisor_gasto_id',
    'time_zone',
    'logo',
    'sku_prefix',
    'enable_product_expiry',
    'expiry_type',
    'on_product_expiry',
    'stock_expiry_alert_days',
    'enable_brand',
    'enable_category',
    'default_unit',
    'enable_sub_units',
    'enable_racks',
    'enable_row',
    'enable_editing_product_from_purchase',
    'enable_inline_tax',
    'enable_inline_tax_purchase',
    'currency_symbol_placement',
    'host_smpt',
    'user_smtp',
    'pass_smtp',
    'puerto_smpt',
    'smtp_encryptation',
    'email_notificacion_smtp',
    'proveedorSistemas',
    'registro_medicamento',
    'forma_farmaceutica',
    'notification_email',
    'host_imap',
    'user_imap',
    'pass_imap',
    'puerto_imap',
    'imap_encryptation',
    'active',
  ];

  public function currency()
  {
    return $this->belongsTo(Currency::class);
  }

  public function owner()
  {
    return $this->belongsTo(User::class, 'owner_id');
  }

  public function customerGroups()
  {
    return $this->hasMany(CustomerGroup::class);
  }

  public function businessLocations()
  {
    return $this->hasMany(BusinessLocation::class);
  }

  public function customerCalculoRegistros()
  {
    return $this->belongsToMany(
      Contact::class,                      // Modelo destino
      'business_customer_calculo_registros', // Nombre de la tabla pivote
      'business_id',                      // Foreign key en la tabla pivote hacia este modelo
      'contact_id'                        // Foreign key en la tabla pivote hacia Contact
    );
  }
}
