<style>
    h3 {
        font-size: 20px !important;
        padding: 15px 0;
    }
</style>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('budgets.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Dados Gerais --}}
                <div class="mb-3">
                    <label>Título do Orçamento</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                </div>

                <div class="mb-3">
                    <label>Descrição do Orçamento</label>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

                <hr class="my-3">

                {{-- Dados do Cliente --}}
                <h3>Dados do Cliente</h3>
                <div class="mb-3">
                    <label>Cliente</label>
                    <input type="text" name="client_name" class="form-control" required
                        value="{{ old('client_name') }}">
                </div>
                <div class="mb-3">
                    <label>WhatsApp</label>
                    <input type="text" name="client_whatsapp" class="form-control"
                        value="{{ old('client_whatsapp') }}">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="client_email" class="form-control" value="{{ old('client_email') }}">
                </div>
                <div class="mb-3">
                    <label>Endereço</label>
                    <input type="text" name="client_address" class="form-control"
                        value="{{ old('client_address') }}">
                </div>
                <div class="mb-3">
                    <label>Data</label>
                    <input type="date" name="date" class="form-control" required value="{{ old('date') }}">
                </div>

                {{-- Itens --}}
                <h3>Itens</h3>
                <div id="items-container">
                    <div class="item-card border rounded p-3 mb-3">
                        <div class="mb-2">
                            <label>Imagem</label>
                            <input type="file" name="items[0][image]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Nome</label>
                            <input type="text" name="items[0][name]" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Descrição</label>
                            <input type="text" name="items[0][description]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Valor</label>
                            <input type="text" name="items[0][price]" class="form-control price" required>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remover</button>
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-sm my-2" id="add-item">Adicionar Item</button>

                <hr>

                {{-- Pagamento --}}
                <h3>Formas de Pagamento</h3>
                <div class="form-check">
                    <input class="form-check-input payment" type="checkbox" name="payment_methods[]" value="pix"
                        id="pix"
                        {{ is_array(old('payment_methods')) && in_array('pix', old('payment_methods')) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pix">Pix</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input payment" type="checkbox" name="payment_methods[]" value="boleto"
                        id="boleto"
                        {{ is_array(old('payment_methods')) && in_array('boleto', old('payment_methods')) ? 'checked' : '' }}>
                    <label class="form-check-label" for="boleto">Boleto</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input payment" type="checkbox" name="payment_methods[]" value="cartao"
                        id="cartao"
                        {{ is_array(old('payment_methods')) && in_array('cartao', old('payment_methods')) ? 'checked' : '' }}>
                    <label class="form-check-label" for="cartao">Cartão de Crédito</label>
                </div>

                <div id="discounts-container" class="mt-3"></div>

                <div id="installments-container" class="mt-3" style="display: none;">
                    <label>Parcelas (Cartão)</label>
                    <select name="installments" class="form-select">
                        <option value="">Selecione</option>
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ old('installments') == $i ? 'selected' : '' }}>
                                {{ $i }}x
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="mb-3">
                    <label>Validade do Orçamento*</label>
                    <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}"
                        required>
                </div>

                <div class="mb-3">
                    <label>Prazo de Entrega</label>
                    <input type="text" name="delivery_time" class="form-control"
                        value="{{ old('delivery_time') }}">
                </div>

                {{-- Total --}}
                <div class="mt-3">
                    <strong>Total Geral:</strong> <span id="total">R$ 0,00</span>
                </div>
                <div id="payment-totals" class="mt-3"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button class="btn btn-primary">Gerar e Enviar</button>
            </div>
        </form>
    </div>
</div>


{{-- Scripts --}}
<script src="https://unpkg.com/imask"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let index = 1;

        // Máscara WhatsApp
        const whatsapp = document.querySelector('input[name="client_whatsapp"]');
        IMask(whatsapp, {
            mask: '(00) 00000-0000'
        });

        aplicarMascaraValor();
        calculateTotal();

        // Adicionar Item
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const card = `
                <div class="item-card border rounded p-3 mb-3">
                    <div class="mb-2">
                        <label>Imagem</label>
                        <input type="file" name="items[${index}][image]" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Nome</label>
                        <input type="text" name="items[${index}][name]" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Descrição</label>
                        <input type="text" name="items[${index}][description]" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Valor</label>
                        <input type="text" name="items[${index}][price]" class="form-control price" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-item">Remover</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', card);
            index++;

            aplicarMascaraValor();
            calculateTotal();
        });

        // Remover Item
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.item-card').remove();
                calculateTotal();
            }
        });

        // Calcular Total e Totais por Pagamento sempre que alterar valores ou descontos
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('price') || e.target.name.startsWith('discounts')) {
                calculateTotal();
            }
        });

        // Atualizar descontos e parcelas ao selecionar métodos de pagamento
        document.querySelectorAll('.payment').forEach(input => {
            input.addEventListener('change', function() {
                const container = document.getElementById('discounts-container');
                container.innerHTML = '';
                document.getElementById('installments-container').style.display = 'none';

                document.querySelectorAll('.payment:checked').forEach(pm => {
                    container.innerHTML += `
                        <div class="mb-2">
                            <label>Desconto ${pm.value.charAt(0).toUpperCase() + pm.value.slice(1)} (%)</label>
                            <input type="number" name="discounts[${pm.value}]" step="0.01" class="form-control" placeholder="Opcional">
                        </div>
                    `;

                    if (pm.value === 'cartao') {
                        document.getElementById('installments-container').style
                            .display = 'block';
                    }
                });

                calculateTotal();
            });
        });

        // Função para aplicar máscara de valores
        function aplicarMascaraValor() {
            document.querySelectorAll('.price').forEach(function(input) {
                if (!input.classList.contains('imask-initialized')) {
                    IMask(input, {
                        mask: 'R$ num',
                        blocks: {
                            num: {
                                mask: Number,
                                thousandsSeparator: '.',
                                radix: ',',
                                scale: 2,
                                padFractionalZeros: true,
                                normalizeZeros: true,
                                mapToRadix: ['.']
                            }
                        }
                    });
                    input.classList.add('imask-initialized');
                }
            });
        }

        // Calcular total geral
        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.price').forEach(input => {
                const valor = input.value
                    .replace('R$ ', '')
                    .replace(/\./g, '')
                    .replace(',', '.');
                total += parseFloat(valor) || 0;
            });

            document.getElementById('total').innerText = total.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });

            updatePaymentTotals(total);
        }

        // Calcular e exibir totais por forma de pagamento com descontos
        function updatePaymentTotals(total) {
            const container = document.getElementById('payment-totals');
            if (!container) return;

            container.innerHTML = '';

            const methods = document.querySelectorAll('.payment:checked');

            if (methods.length === 0) return;

            let html = `<h5 class="mt-3">Totais por Forma de Pagamento:</h5><ul class="list-group">`;

            methods.forEach(method => {
                const discountInput = document.querySelector(
                    `input[name="discounts[${method.value}]"]`);
                let discount = discountInput ? parseFloat(discountInput.value) : 0;

                if (isNaN(discount)) discount = 0;

                const discountedTotal = total - (total * (discount / 100));

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${method.labels[0].innerText} 
                        <span><strong>${discount > 0 ? `(com ${discount}% de desconto)` : ''} ${discountedTotal.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}</strong></span>
                    </li>
                `;
            });

            html += `</ul>`;
            container.innerHTML = html;
        }
    });
</script>
