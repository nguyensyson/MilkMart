<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PlaceholderController extends Controller
{
    public function suppliers(): Response
    {
        return $this->render('Quản lý nhà cung cấp');
    }

    public function reports(): Response
    {
        return $this->render('Báo cáo & thống kê');
    }

    private function render(string $title): Response
    {
        return Inertia::render('Admin/Placeholder', [
            'title' => $title,
        ]);
    }
}
