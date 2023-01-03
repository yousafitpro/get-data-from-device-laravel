<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class employeeCOntroller extends Controller
{
    public function addView()
    {
        $data['roles']=roleController::$employee_roles;
        return view('employee.add',$data);
    }
    public function store(Request $request)
    {

        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Employee added successfully.',
                    'type' => 'success',
                ]
            ]);
    }
}
