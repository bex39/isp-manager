@extends('customers.layouts.app')

@section('title', 'Create Ticket')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('customer.tickets.index') }}" class="btn btn-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Back to Tickets
        </a>
        <h2 class="fw-bold">Create Support Ticket</h2>
        <p class="text-muted">Describe your issue and we'll help you resolve it</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('customer.tickets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="Brief description of your issue" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical Issue</option>
                            <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing Question</option>
                            <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General Inquiry</option>
                            <option value="complaint" {{ old('category') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="">-- Select Priority --</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - General question</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }} selected>Medium - Can wait a few hours</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Need response soon</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent - Service down/critical</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="6" placeholder="Please describe your issue in detail..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Be as specific as possible to help us resolve your issue faster</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Submit Ticket
                        </button>
                        <a href="{{ route('customer.tickets.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tips for Better Support</h6>
                <ul class="small">
                    <li class="mb-2">Choose the correct category for faster routing</li>
                    <li class="mb-2">Be specific about the problem you're experiencing</li>
                    <li class="mb-2">Include error messages if any</li>
                    <li class="mb-2">Mention when the issue started</li>
                    <li class="mb-2">Our team typically responds within 24 hours</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 bg-primary text-white mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Need Urgent Help?</h6>
                <p class="small mb-2">For critical service issues, you can also contact us at:</p>
                <p class="mb-0">
                    <i class="bi bi-telephone"></i> 0361-1234567<br>
                    <i class="bi bi-whatsapp"></i> +62 812-3456-7890
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
