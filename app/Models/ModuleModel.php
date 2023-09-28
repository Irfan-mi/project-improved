<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $table = 'modules';

    public function getModuleName($moduleId)
    {
        $query = $this->where('module_id', $moduleId)->first();

        if ($query !== null) {
            return lang($query['name_lang_key']);
        }

        return lang('error_lang.error_unknown');
    }

    public function getModuleDescription($moduleId)
    {
        $query = $this->where('module_id', $moduleId)->first();

        if ($query !== null) {
            return lang($query['desc_lang_key']);
        }

        return lang('error_lang.error_unknown');
    }

    public function getAllPermissions()
    {
        return $this->db->table('ospos_permissions')->get()->getResult();
    }

    public function getAllSubpermissions()
    {
        $builder = $this->db->table('permissions')
            ->join('ospos_modules', 'ospos_modules.module_id = ospos_permissions.module_id')
            ->where('ospos_modules.module_id !=', 'ospos_permissions.permission_id');

        return $builder->get()->getResult();
    }

    public function getAllModules()
    {
        return $this->db->table($this->table)->orderBy('sort', 'ASC')->get()->getResult();
    }

    public function getAllowedModules($personId)
    {
        $builder = $this->db->table($this->table)
            ->join('ospos_permissions', 'ospos_permissions.permission_id = ospos_modules.module_id')
            ->join('ospos_grants', 'ospos_permissions.permission_id = ospos_grants.permission_id')
            ->where('person_id', $personId)
            ->orderBy('sort', 'ASC');

        return $builder->get()->getResult();
    }
}
