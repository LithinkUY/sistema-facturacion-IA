<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPedidoLine extends Model
{
    protected $table = 'order_pedido_lines';

    protected $guarded = ['id'];

    // ===== Relaciones =====

    public function orderPedido()
    {
        return $this->belongsTo(OrderPedido::class, 'order_pedido_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    // ===== Accessors =====

    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->quantity_received);
    }

    public function getIsFullyReceivedAttribute()
    {
        return $this->quantity_received >= $this->quantity;
    }

    public function getReceivedPercentAttribute()
    {
        return $this->quantity > 0 
            ? round(($this->quantity_received / $this->quantity) * 100) 
            : 0;
    }

    // ===== Helpers =====

    public function calculateLineTotal()
    {
        $subtotal = $this->quantity * $this->unit_price;
        
        // Aplicar descuento
        $discount = 0;
        if ($this->discount_percent > 0) {
            $discount = $subtotal * ($this->discount_percent / 100);
        }
        $this->discount_amount = $discount;
        
        // Aplicar impuesto
        $taxable = $subtotal - $discount;
        $tax = $taxable * ($this->tax_percent / 100);
        $this->tax_amount = $tax;
        
        $this->line_total = $taxable + $tax;
        
        return $this;
    }
}
