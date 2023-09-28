<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{

    protected $table = 'employees';
    protected $allowedFields = ['username', 'password', 'person_id', 'deleted', 'hash_version'];

    public function exists($personId)
    {
        $this->join('people', 'people.person_id = employees.person_id')
            ->where('employees.person_id', $personId);

        return ($this->get()->getNumRows() === 1);
    }

    public function onlineExists($personId)
    {
        $online = \Config\Database::connect('online');

        $result = $online->table($this->table)
            ->join('people', 'people.person_id = employees.person_id')
            ->where('employees.person_id', $personId)
            ->get();

        return (count($result->getResult()) === 1);
    }

    public function getAll($limit = 10000, $offset = 0)
    {
        $this->join('people', 'employees.person_id = people.person_id')
            ->where('deleted', 0)
            ->orderBy('last_name', 'ASC')
            ->limit($limit, $offset);

        return $this->get()->getResult();
    }

    public function getLoggedInEmployeeInfo()
    {
        if ($this->isLoggedIn()) {
            return $this->getEmployeeInfo($this->session()->get('person_id'));
        }

        return false;
    }

    public function isLoggedIn()
    {
        return $this->session->has('person_id');
    }

    public function hasModuleGrant($permissionId, $personId)
    {
        $query = $this->db->table('grants');
        $query->like('permission_id', $permissionId, 'after');
        $query->where('person_id', $personId);

        $resultCount = $query->countAllResults();

        if ($resultCount !== 1) {
            return $resultCount !== 0;
        }

        return $this->hasSubpermissions($permissionId);
    }

    public function hasSubpermissions($permissionId)
    {
        $query = $this->db->table('permissions'); // Change this to your 'permissions' table name
        $query->like('permission_id', $permissionId . '_', 'after');

        return $query->countAllResults() === 0;
    }

    public function hasGrant($permissionId, $personId)
    {
        if ($permissionId === null) {
            return true;
        }

        $query = $this->db->table('grants')
            ->where('person_id', $personId)
            ->where('permission_id', $permissionId)
            ->get();

        return ($query->getNumRows() === 1);
    }

    public function getEmployeeInfo($employeeIds)
    {
        $this->join('people', 'people.person_id = employees.person_id')
            ->where('employees.person_id', $employeeIds);

        $query = $this->get();

        if ($query->getNumRows() === 1) {
            return $query->getRow();
        } else {
            $personModel = new PersonModel();

            $employeeFields = $this->getFieldNames($this->table);
            foreach ($employeeFields as $field) {
                $personModel->$field = '';
            }

            return $personModel;
        }
    }

    public function getMultipleInfo($employeeIds)
    {
        return $this->join('people', 'people.person_id = employees.person_id')
            ->whereIn('employees.person_id', $employeeIds)
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResult();
    }

    public function getTotalRows()
    {
        return $this->where('deleted', 0)->countAllResults();
    }
}
