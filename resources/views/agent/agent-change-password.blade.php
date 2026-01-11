@extends('layouts.agent-layout')

@section('title', 'Change Password - Dream Mulk')

@section('styles')
<style>
    .password-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, #303b97 0%, #1e2875 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        color: white;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }

    .form-container {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
    }

    .alert-success {
        background: #d1fae5;
        border: 2px solid #059669;
        color: #059669;
    }

    .alert-error {
        background: #fee2e2;
        border: 2px solid #ef4444;
        color: #ef4444;
    }

    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .info-box i {
        color: #3b82f6;
        font-size: 20px;
        margin-top: 2px;
    }

    .info-box-text {
        font-size: 13px;
        color: #1e40af;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 4px;
    }

    .password-input-wrapper {
        position: relative;
    }

    .form-input {
        width: 100%;
        padding: 12px 48px 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
        background: white;
    }

    .form-input:focus {
        outline: none;
        border-color: #303b97;
        box-shadow: 0 0 0 3px rgba(48,59,151,0.1);
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 16px;
        padding: 8px;
    }

    .password-toggle:hover {
        color: #303b97;
    }

    .password-requirements {
        margin-top: 8px;
        font-size: 12px;
        color: #64748b;
    }

    .password-requirements ul {
        margin: 8px 0 0 0;
        padding-left: 20px;
    }

    .password-requirements li {
        margin: 4px 0;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        margin-top: 24px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: #303b97;
        color: white;
        box-shadow: 0 4px 12px rgba(48,59,151,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(48,59,151,0.4);
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    @media (max-width: 768px) {
        .password-container {
            padding: 16px;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="password-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-key"></i> Change Password
        </h1>
        <p class="page-subtitle">Update your account password</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('agent.password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-container">
            <div class="info-box">
                <i class="fas fa-shield-alt"></i>
                <div class="info-box-text">
                    <strong>Security Tip:</strong> Use a strong password with at least 8 characters, including uppercase, lowercase, numbers, and special characters.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Current Password<span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="current_password" id="currentPassword" class="form-input" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                        <i class="fas fa-eye" id="currentPassword-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">New Password<span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password" id="newPassword" class="form-input" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                        <i class="fas fa-eye" id="newPassword-icon"></i>
                    </button>
                </div>
                <div class="password-requirements">
                    Password must contain:
                    <ul>
                        <li>At least 8 characters</li>
                        <li>One uppercase letter</li>
                        <li>One lowercase letter</li>
                        <li>One number</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password<span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password_confirmation" id="confirmPassword" class="form-input" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                        <i class="fas fa-eye" id="confirmPassword-icon"></i>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('agent.profile', auth('agent')->user()->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
