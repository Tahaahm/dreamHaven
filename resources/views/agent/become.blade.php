@extends('layouts.app')

@section('content')
<div class="container my-5">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="card-title mb-3 text-center fw-bold text-primary">Become an Agent</h2>
                    <p class="text-center text-muted mb-4">
                        Hello, <span class="fw-semibold">{{ $user->username }}</span>. You are not an agent yet. Complete the form below to join our platform.
                    </p>

                    <form action="{{ route('agent.create.from.user.submit') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        {{-- Agent Name --}}
                        <div class="mb-3">
                            <label for="agent_name" class="form-label fw-semibold">Agent Name</label>
                            <input type="text" class="form-control rounded-3" id="agent_name" name="agent_name" value="{{ old('agent_name', $user->username) }}" required>
                            <div class="invalid-feedback">
                                Please enter your agent name.
                            </div>
                        </div>

                        {{-- Bio --}}
                   <div class="mb-3">
    <label for="agent_bio" class="form-label fw-semibold">Bio</label>
    <textarea class="form-control rounded-3 @error('agent_bio') is-invalid @enderror" 
              id="agent_bio" 
              name="agent_bio" 
              rows="4" 
              placeholder="Tell us about yourself">{{ old('agent_bio', $user->about_me) }}</textarea>
    @error('agent_bio')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


                        {{-- Optional fields can be added here --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="primary_email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control rounded-3" id="primary_email" name="primary_email" value="{{ $user->email }}" required>
                            </div>


                        <div class="col-md-6">
    <label for="primary_phone" class="form-label fw-semibold">Phone</label>
    <div class="form-group">
        <input type="text" 
               name="primary_phone" 
               id="primary_phone"
               class="form-control @error('primary_phone') is-invalid @enderror"
               value="{{ old('primary_phone', $user->phone) }}"
               required>
        @error('primary_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>



                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm">Become Agent</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Optional: Bootstrap form validation --}}
<script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })()
</script>
@endsection
