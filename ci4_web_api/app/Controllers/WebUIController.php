<?php

namespace App\Controllers;

class WebUIController extends BaseController
{
    // Main view
    public function index(): string
    {
        return view('sidebar');
    }
    // Dashboard view
    public function dashboard()
    {
        $data['active_menu'] = 'dashboard';
        return view('dashboard/dashboard', $data);
    }
     // Team view
     public function team()
     {
        $data['active_menu'] = 'team';
         return view('team/team', $data);
     }
      // Task view
    public function task()
    {
        $data['active_menu'] = 'task';
        return view('task/task', $data);
    }
    // Team Detail view
    public function teamDetail()
    {
        $data['active_menu'] = 'team';
        return view('team_detail/team_detail', $data);
    }
    // Task Detail View
    public function taskDetail()
    {
        $data['active_menu'] = 'task';
        return view('task_detail/task_detail', $data);
    }
}
