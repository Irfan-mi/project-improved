<?php

namespace App\Models;

use CodeIgniter\Model;

class AppConfigModel extends Model
{
    protected $table = 'app_config';
    protected $primaryKey = 'key';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    public function exists($key)
    {
        return $this->where('key', $key)->countAllResults() === 1;
    }

    public function getAll()
    {
        $results = $this->orderBy('key', 'ASC')->findAll();

        $data = [];
        foreach ($results as $row) {
            $data[$row['key']] = $row['value'];
        }

        return $data;
    }

    public function get($key)
    {
        $query = $this->where('key', $key)->first();

        if ($query !== null) {
            return $query['value'];
        }

        return '';
    }

    public function saveAppConfig($key, $value)
    {
        $configData = [
            'key'   => $key,
            'value' => $value
        ];

        if (!$this->exists($key)) {
            return $this->insert($configData);
        }

        return $this->update($key, $configData);
    }

    public function batchSave($data)
    {
        $success = true;

        // Run these queries as a transaction, we want to make sure we do all or nothing
        $this->transStart();

        foreach ($data as $key => $value) {
            $success &= $this->saveAppConfig($key, $value);
        }

        $this->transComplete();

        $success &= $this->transStatus();

        return $success;
    }

    public function deleteAppConfig($key)
    {
        return $this->where('key', $key)->delete();
    }

    public function deleteAll()
    {
        return $this->emptyTable();
    }

}