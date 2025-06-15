<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orçamento</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .container {
            padding: 30px;
        }

        .header {
            background-color: #1a5c88;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h2 {
            margin-bottom: 5px;
        }

        .section {
            margin-top: 20px;
        }

        .section h3 {
            color: #1a5c88;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .section p {
            font-size: 14px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #eee;
            border-radius: 5px;
        }

        .payment {
            margin-bottom: 5px;
        }

        .total {
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
        }

        .image {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h2>Proposta Comercial</h2>
            <p>{{ $budget->date->format('d/m/Y') }}</p>
        </div>

        {{-- Dados do Cliente --}}
        <div class="section">
            <h3>Dados do Cliente</h3>
            <p><strong>Nome:</strong> {{ $budget->client_name }}</p>
            @if ($budget->client_whatsapp)
                <p><strong>WhatsApp:</strong> {{ $budget->client_whatsapp }}</p>
            @endif
            @if ($budget->client_email)
                <p><strong>Email:</strong> {{ $budget->client_email }}</p>
            @endif
            @if ($budget->client_address)
                <p><strong>Endereço:</strong> {{ $budget->client_address }}</p>
            @endif
        </div>

        {{-- Título e Descrição --}}
        @if ($budget->title)
            <div class="section">
                <h3>Título do Orçamento</h3>
                <p>{{ $budget->title }}</p>
            </div>
        @endif

        @if ($budget->description)
            <div class="section">
                <h3>Descrição do Orçamento</h3>
                <p>{{ $budget->description }}</p>
            </div>
        @endif

        {{-- Itens --}}
        <div class="section">
            <h3>Produtos / Serviços</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($budget->items as $item)
                        <tr>
                            <td>
                                @if ($item->image)
                                    <img src="{{ public_path('storage/' . $item->image) }}"
                                        style="width: 160px; object-fit: cover;">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->description }}</td>
                            <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="total">Total: R$ {{ number_format($budget->total, 2, ',', '.') }}</p>
        </div>

        {{-- Formas de Pagamento --}}
        <div class="section">
            <h3>Formas de Pagamento</h3>

            @foreach ($budget->payment_methods as $method)
                @php
                    $discount = $budget->discounts[$method] ?? 0;
                    $discountValue = $budget->total * ($discount / 100);
                    $finalValue = $budget->total - $discountValue;
                @endphp

                <div class="payment">
                    <strong>
                        {{ ucfirst($method) }}
                        @if ($discount)
                            - {{ $discount }}% de desconto
                        @endif
                    </strong><br>
                    Valor: <strong>R$ {{ number_format($finalValue, 2, ',', '.') }}</strong>

                    @if ($method == 'cartao' && $budget->installments)
                        <div style="margin-top: 5px;">
                            <span>
                                Parcelado em <strong>{{ $budget->installments }}x de
                                    R$ {{ number_format($finalValue / $budget->installments, 2, ',', '.') }}</strong>
                            </span><br>
                            <small style="color: #888;">
                                *Valor aproximado. As taxas e encargos da operadora de cartão serão calculados no
                                momento da compra.
                            </small>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- Validade e Prazo --}}
        <div class="section">
            <h3>Validade e Prazo</h3>
            <p><strong>Validade do Orçamento:</strong>
                {{ \Carbon\Carbon::parse($budget->valid_until)->format('d/m/Y') }}</p>
            @if ($budget->delivery_time)
                <p><strong>Prazo de Entrega:</strong> {{ $budget->delivery_time }}</p>
            @endif
        </div>
    </div>
</body>

</html>
