<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Libraries\GuLibrary;
use App\Models\EmployeeModel;
use App\Models\ModuleModel;

class Home extends BaseController
{
    public function index($moduleId = null, $subModuleId = null): string
    {
        $gu = new GuLibrary();
        $employeeModel = new EmployeeModel();
        $moduleModel = new ModuleModel();

        if (!$employeeModel->isLoggedIn()) {
            return redirect()->route('login');
        }

        $getLoggedInEmployeeInfo = $employeeModel->getLoggedInEmployeeInfo();

        if (!$employeeModel->hasModuleGrant($moduleId, $getLoggedInEmployeeInfo->person_id) ||
            (isset($submoduleId) && !$employeeModel->hasModuleGrant($submoduleId, $getLoggedInEmployeeInfo->person_id))
        ) {
            redirect()->route('noAccess/' . $moduleId . '/' . $subModuleId);
        }

        $allowedModules = $moduleModel->getAllowedModules($getLoggedInEmployeeInfo->person_id);

        $data['allowedModules'] = $allowedModules;
        $data['userInfo'] = $getLoggedInEmployeeInfo;
        $data['controllerName'] = $moduleId;
        $data['appData'] = $this->appData;
        $data['gu'] = $gu;
        return view('home', $data);
    }

    public function logout()
    {
        $employeeModel = new EmployeeModel();
        $employeeModel->logout();
    }
}
