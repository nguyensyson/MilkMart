<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Brand names sourced from Vinamilk's public brand listing
     * (vinamilk.com.vn/brands/*), used as sample data for local dev.
     *
     * Vinamilk does not expose a per-brand logo asset separate from
     * product photos, so logo_url / description are intentionally left
     * null rather than guessed.
     */
    public function run(): void
    {
        $brands = [
            '100%',
            'ADM',
            'Dielac',
            'Green Farm',
            'HAYĐẤY',
            'Hero',
            'Mộc Châu Creamery',
            'Ngôi Sao Phương Nam',
            'Optimum',
            'Ridielac',
            'Ridielac Gold',
            'SuSu',
            'Sure',
            'Tài Lộc',
            'Vinamilk',
            'Vinamilk Flex',
            'Vinamilk Happy Star',
            'YokoGold',
            'Yomilk',
            'Ông Thọ',
        ];

        foreach ($brands as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }
    }
}
