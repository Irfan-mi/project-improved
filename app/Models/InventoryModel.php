<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'trans_id';
    protected $allowedFields = ['trans_items', 'trans_user', 'trans_date', 'trans_comment', 'trans_location', 'trans_inventory'];

    public function insertInventory($inventoryData)
    {
        return $this->db->table($this->table)->insert($inventoryData);
    }

    public function getInventoryDataForItem($itemId, $locationId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('trans_items', $itemId)
            ->orderBy('trans_date', 'DESC');

        if ($locationId !== null) {
            $builder->where('trans_location', $locationId);
        }

        return $builder->get()->getResult();
    }
}
