<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BudgetController extends Controller
{

    public function index(Request $request)
    {
        $query = Budget::where('user_id', auth()->id());

        // Filtro por nome do cliente
        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', '%' . $request->client_name . '%');
        }

        // Filtro por data
        if ($request->filled('date_start')) {
            $query->whereDate('date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('date', '<=', $request->date_end);
        }


        $budgets = $query->latest()->paginate(6);

        return view('dashboard', compact('budgets'));
    }

    public function store(Request $request)
    {
        try {

            $items = $request->input('items', []);

            foreach ($items as $index => $item) {
                if (isset($item['price'])) {
                    $items[$index]['price'] = str_replace(['R$', '.', ','], ['', '', '.'], $item['price']);
                }
            }

            $request->merge(['items' => $items]);
            $data = $request->validate([
                'client_whatsapp' => 'nullable|string',
                'client_email' => 'nullable|email',
                'client_address' => 'nullable|string',
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'client_name' => 'required|string',
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.name' => 'required|string',
                'items.*.description' => 'nullable|string',
                'items.*.price' => 'required|numeric',
                'items.*.image' => 'nullable|image',
                'payment_methods' => 'required|array',
                'discounts' => 'nullable|array',
                'installments' => 'nullable|integer',
                'valid_until' => 'required|date',
                'delivery_time' => 'nullable|string',
            ]);

            $total = collect($data['items'])->sum('price');

            $budget = Budget::create([
                'user_id' => auth()->id(),
                'client_name' => $data['client_name'],
                'client_whatsapp' => $data['client_whatsapp'],
                'client_email' => $data['client_email'],
                'client_address' => $data['client_address'],
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'total' => $total,
                'payment_methods' => $data['payment_methods'],
                'discounts' => $data['discounts'] ?? [],
                'installments' => $data['installments'],
                'status' => 'enviado',
                'valid_until' => $data['valid_until'],
                'delivery_time' => $data['delivery_time'],
            ]);

            foreach ($data['items'] as $index => $item) {
                $imagePath = null;
                if ($request->hasFile("items.$index.image")) {
                    $imagePath = $request->file("items.$index.image")->store('items', 'public');
                }

                $budget->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image' => $imagePath,
                ]);
            }

            // Gerar PDF
            $pdf = Pdf::loadView('pdf.budget', compact('budget'));
            $pdfPath = 'budgets/budget_' . $budget->id . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $budget->update(['pdf_path' => $pdfPath]);

            $url = asset('storage/' . $pdfPath);

            $message =
                "*Orçamento Gerado!*\n\n" .
                "Olá! Segue o link do seu orçamento.\n" .
                "Acesse abaixo para visualizar todos os detalhes:\n\n" .
                "$url\n\n" .
                "Qualquer dúvida, estou à disposição.\n\n" .
                '*Agradecemos pela preferência!*';
            $whatsUrl = 'https://wa.me/?text=' . rawurlencode($message);


            $emailSubject = "Orçamento para {$budget->client_name}";
            $emailBody = "Olá, {$budget->client_name}. Segue seu orçamento no link: {$url}";
            $mailTo = "mailto:{$budget->client_email}?subject=" . urlencode($emailSubject) . "&body=" . urlencode($emailBody);

            return redirect()->route('dashboard')
                ->with('success', 'Orçamento criado com sucesso!')
                ->with('whatsapp', $whatsUrl)
                ->with('email', $mailTo);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao gerar o orçamento: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:enviado,aprovado,reprovado,negociando',
        ]);

        $budget = Budget::findOrFail($id);
        $budget->status = $request->status;
        $budget->save();

        return redirect()->back()->with('success', 'Status atualizado!');
    }
}
