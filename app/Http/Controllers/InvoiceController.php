<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $invoices = Invoice::where('user_id', $request->user()?->id)
            ->latest('created_at')
            ->get();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }
}
