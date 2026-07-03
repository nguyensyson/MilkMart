<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Product categories sourced from Vinamilk's public collection listing
     * (vinamilk.com.vn/collections/*), used as sample data for local dev.
     */
    public function run(): void
    {
        $categories = [
            'Bột ăn dặm',
            'Nước giải khát',
            'Sữa Chua Sấy Thăng Hoa',
            'Sữa bột người lớn',
            'Sữa bột trẻ em',
            'Sữa chua uống',
            'Sữa dinh dưỡng',
            'Sữa thực vật',
            'Sữa trái cây',
            'Sữa tươi',
            'Sữa đặc',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
