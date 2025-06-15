<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orçamentos') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="my-4">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    Novo Orçamento
                </button>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success m-2">
                    {{ session('success') }}<br>
                    @if (session('whatsapp'))
                        <a href="{{ session('whatsapp') }}" target="_blank" class="btn btn-success mt-2">
                            Enviar pelo WhatsApp
                        </a>
                    @endif
                    @if (session('email'))
                        <a href="{{ session('email') }}" class="btn btn-primary mt-2">
                            Enviar por Email
                        </a>
                    @endif
                </div>
            @endif
            <div class="card mb-4 m-2">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard') }}" class="row g-2">
                        <div class="col-md-4">
                            <label>Nome do Cliente</label>
                            <input type="text" name="client_name" class="form-control"
                                value="{{ request('client_name') }}" placeholder="Buscar cliente...">
                        </div>

                        <div class="col-md-2">
                            <label>Data Inicial</label>
                            <input type="date" name="date_start" class="form-control"
                                value="{{ request('date_start') }}">
                        </div>

                        <div class="col-md-2">
                            <label>Data Final</label>
                            <input type="date" name="date_end" class="form-control"
                                value="{{ request('date_end') }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Filtrar</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-danger w-100 me-2">Limpar Filtros</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @foreach ($budgets as $budget)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100 m-2">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title">{{ $budget->client_name }}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        {{ \Carbon\Carbon::parse($budget->date)->format('d/m/Y') }}
                                    </h6>

                                    @if ($budget->title)
                                        <p class="fw-bold">{{ $budget->title }}</p>
                                    @endif
                                    @if ($budget->description)
                                        <p>{{ $budget->description }}</p>
                                    @endif

                                    <p><strong>Valor:</strong> R$ {{ number_format($budget->total, 2, ',', '.') }}</p>

                                    <span
                                        class="badge
                                        @if ($budget->status == 'enviado') bg-primary
                                        @elseif($budget->status == 'negociando') bg-warning text-dark
                                        @elseif($budget->status == 'aprovado') bg-success
                                        @else bg-danger @endif">
                                        {{ ucfirst($budget->status) }}
                                    </span>

                                    <div class="mt-3">
                                        @if ($budget->pdf_path)
                                            <a href="{{ asset('storage/' . $budget->pdf_path) }}" target="_blank"
                                                class="btn btn-sm btn-secondary mb-1">
                                                Ver PDF
                                            </a>
                                            @php
                                                $url = asset('storage/' . $budget->pdf_path);

                                                $message =
                                                    "*Orçamento Gerado!*\n\n" .
                                                    "Olá! Segue o link do seu orçamento.\n" .
                                                    "Acesse abaixo para visualizar todos os detalhes:\n\n" .
                                                    "$url\n\n" .
                                                    "Qualquer dúvida, estou à disposição.\n\n" .
                                                    '*Agradecemos pela preferência!*';
                                            @endphp

                                            <a href="https://wa.me/?text={{ rawurlencode($message) }}" target="_blank"
                                                class="btn btn-sm btn-success mb-1">
                                                WhatsApp
                                            </a>



                                            @if ($budget->client_email)
                                                <a href="mailto:{{ $budget->client_email }}?subject={{ urlencode('Orçamento para ' . $budget->client_name) }}&body={{ urlencode('Olá, ' . $budget->client_name . '. Segue seu orçamento: ' . asset('storage/' . $budget->pdf_path)) }}"
                                                    class="btn btn-sm btn-primary mb-1">
                                                    Email
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <form action="{{ route('budgets.updateStatus', $budget->id) }}" method="POST"
                                    class="mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label small">Status:</label>
                                    <select name="status" onchange="this.form.submit()"
                                        class="form-select form-select-sm">
                                        <option {{ $budget->status == 'enviado' ? 'selected' : '' }} value="enviado">
                                            Enviado</option>
                                        <option {{ $budget->status == 'negociando' ? 'selected' : '' }}
                                            value="negociando">Negociando</option>
                                        <option {{ $budget->status == 'aprovado' ? 'selected' : '' }} value="aprovado">
                                            Aprovado</option>
                                        <option {{ $budget->status == 'reprovado' ? 'selected' : '' }}
                                            value="reprovado">
                                            Reprovado</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="row d-flex justify-content-center my-4">

                    {{ $budgets->withQueryString()->links() }}

                </div>
            </div>
        </div>
    </div>

    @include('components.budget-modal')

</x-app-layout>
