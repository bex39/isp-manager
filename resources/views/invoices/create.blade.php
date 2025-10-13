@extends('layouts.admin')

@section('title', 'Generate Invoice')
@section('page-title', 'Generate Invoice')

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <!-- Customer Selection -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Customer Information</h5>

                <div class="mb-3">
                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}"
                                    data-package="{{ $cust->package?->name }}"
                                    data-price="{{ $cust->package?->price }}"
                                    {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                {{ $cust->customer_code }} - {{ $cust->name }} ({{ $cust->package?->name ?? 'No Package' }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Invoice Dates -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Invoice Details</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                               value="{{ old('issue_date', date('Y-m-d')) }}" required>
                        @error('issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="custom-table mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Invoice Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                        <i class="bi bi-plus"></i> Add Item
                    </button>
                </div>

                <div id="itemsContainer">
                    <!-- Item Row Template -->
                    <div class="item-row mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label class="form-label small">Description</label>
                                <input type="text" name="items[0][description]" class="form-control" placeholder="Monthly Internet Package" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Qty</label>
                                <input type="number" name="items[0][qty]" class="form-control item-qty" value="1" min="1" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small">Price</label>
                                <input type="number" name="items[0][price]" class="form-control item-price" value="0" min="0" step="1000" required>
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-item w-100">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charges -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Additional Charges</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" name="tax_percentage" id="tax_percentage" class="form-control"
                            value="0" min="0" max="100" step="0.01">
                        <small class="text-muted">Contoh: 11 untuk PPN 11%</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount (Rp)</label>
                        <input type="number" name="discount" id="discount" class="form-control"
                            value="0" min="0" step="1000">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

        <div class="col-lg-4">
            <!-- Summary -->
            <div class="custom-table mb-4">
                <h5 class="fw-bold mb-4">Summary</h5>

                <table class="table table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end"><strong id="subtotalDisplay">Rp 0</strong></td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-end" id="taxDisplay">Rp 0</td>
                    </tr>
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end text-danger" id="discountDisplay">- Rp 0</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><h4 class="mb-0" id="totalDisplay">Rp 0</h4></td>
                    </tr>
                </table>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Generate Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemIndex = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Add Item
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const newItem = document.querySelector('.item-row').cloneNode(true);

        // Update indices
        newItem.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name');
            input.setAttribute('name', name.replace('[0]', `[${itemIndex}]`));
            input.value = input.classList.contains('item-qty') ? '1' : '0';
        });

        container.appendChild(newItem);
        itemIndex++;

        attachItemListeners();
        calculateTotal();
    });

    // Remove Item
    function attachItemListeners() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.onclick = function() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    this.closest('.item-row').remove();
                    calculateTotal();
                } else {
                    alert('Minimal harus ada 1 item!');
                }
            };
        });

        // Calculate on change
        document.querySelectorAll('.item-qty, .item-price').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });
    }

    // Calculate Total
    function calculateTotal() {
        let subtotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            subtotal += qty * price;
        });

        const taxPercentage = parseFloat(document.getElementById('tax_percentage').value) || 0;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const total = subtotal + taxAmount - discount;

        document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('taxDisplay').textContent = 'Rp ' + taxAmount.toLocaleString('id-ID') + ' (' + taxPercentage + '%)';
        document.getElementById('discountDisplay').textContent = '- Rp ' + discount.toLocaleString('id-ID');
        document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    // Update listener
    document.getElementById('tax_percentage').addEventListener('input', calculateTotal);

    // Auto-fill when customer selected
    document.getElementById('customer_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const packageName = selected.dataset.package;
        const price = selected.dataset.price;

        if (packageName && price) {
            const firstItem = document.querySelector('.item-row');
            firstItem.querySelector('input[name*="[description]"]').value = packageName;
            firstItem.querySelector('input[name*="[price]"]').value = price;
            calculateTotal();
        }
    });

    document.getElementById('tax').addEventListener('input', calculateTotal);
    document.getElementById('discount').addEventListener('input', calculateTotal);

    attachItemListeners();
    calculateTotal();
});
</script>
@endpush
