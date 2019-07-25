<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const UNASSIGNED_ORDER_STATUS = 'UNASSIGNED';
    const ASSIGNED_ORDER_STATUS = 'TAKEN';

    protected $table = 'orders';

    /**
     * @return \App\Http\Models\Distance
     */
    public function distance()
    {
        return $this->hasOne('App\Http\Models\Distance', 'id', 'distance_id');
    }

    /**
     * @return null|int
     */
    public function getDistance()
    {
        return $this->total_distance ? $this->total_distance : $this->distance->total_distance;
    }

    /**
     * Update order status from UNASSIGNED to TAKEN if order is not already taken
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function acceptOrder($orderId)
    {
        $affectedRows = self::where([
            ['id', '=', $orderId],
            ['status', '=', self::UNASSIGNED_ORDER_STATUS],
        ])
        ->update(['orders.status' => self::ASSIGNED_ORDER_STATUS]);

        return $affectedRows > 0 ? true : false;
    }
}
