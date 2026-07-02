<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PlaceholderController extends Controller
{
    public function products(): Response
    {
        return $this->render('Quản lý sản phẩm');
    }

    public function categories(): Response
    {
        return $this->render('Quản lý danh mục');
    }

    public function brands(): Response
    {
        return $this->render('Quản lý thương hiệu');
    }

    public function orders(): Response
    {
        return $this->render('Quản lý đơn hàng');
    }

    public function vouchers(): Response
    {
        return $this->render('Quản lý voucher');
    }

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
