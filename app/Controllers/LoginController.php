<?php

namespace App\Controllers;

use App\Libraries\GuLibrary;
use App\Libraries\BiometricLibrary;
use App\Models\EmployeeModel;

class LoginController extends BaseController
{
    public function index()
    {
        $gu = new GuLibrary();
        $biometric = new BiometricLibrary();
        $employeeModel = new EmployeeModel();

        if ($employeeModel->isLoggedIn()) {
            return redirect()->route('home');
        } else {
            $this->validation->setRule('username', lang('login_lang.login_username'), 'login_check');

            if (!$this->request->getPost() || !$this->validation->withRequest($this->request)->run()) {
                $token = $biometric->generateToken();

                if ($token) {
                    $this->session->set('CloudABIS_accessToken', $token->accessToken);
                }
                $errors = $this->validation->getErrors();
                $data['errors'] = $errors;
                $data['appData'] = $this->appData;
                $data['gu'] = $gu;

                return view('login', $data);
            } else {
                // check for reporting user only to redirect to report page
                $employeeModel = new EmployeeModel();
                $logged_in_employee_info = $employeeModel->getLoggedInEmployeeInfo();

                if ($logged_in_employee_info->comments == 'reporting-user') {
                    return redirect()->to('reports');
                } else {
                    return redirect()->to('sales');
                }
            }
        }
    }

    public function biometric()
    {
        $biometric = new BiometricLibrary();
        $employeeModel = new EmployeeModel();

        if ($this->session->has('CloudABIS_accessToken')) {
            $token = $this->session->get('CloudABIS_accessToken');
        } else {
            $token = $biometric->generateToken();

            $this->session->set('CloudABIS_accessToken', $token->accessToken);
        }

        $input = $this->request->getPost();
        $isIdentify = $biometric->identify((object) $input, $token);

        if ($isIdentify[1]->operationResult == 'MATCH_FOUND') {
            $userName = $isIdentify[1]->bestResult->id;

            if (!$this->validation->run(['templateXML' => $input['templateXML']], 'biometric_check[' . $userName . ']')) {
                return view('login', ['validation' => $this->validation]);
            }

            // Check for reporting user only to redirect to report page
            $employee = $employeeModel->getLoggedInEmployeeInfo();
            if ($employee->comments == 'reporting-user') {
                return redirect()->to('reports');
            }

            return redirect()->to('sales');
        } else {
            if (!$this->validation->run(['templateXML' => $input['templateXML']], 'biometric_check')) {
                return view('login', ['validation' => $this->validation]);
            }

            return view('login');
        }
    }

}
