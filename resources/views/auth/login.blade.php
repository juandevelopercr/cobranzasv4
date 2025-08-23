@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
<!-- Page -->
@vite('resources/assets/vendor/scss/pages/page-auth.scss')
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros', ['width' => 25, 'withbg' =>
            'var(--bs-primary)'])</span>
        <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
        <!-- Login -->
        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">{{ __('Welcome to') }} {{ config('variables.templateName') }}! 👋</h4>
                <p class="mb-6">{{ __('Log in to your account to start the system') }}</p>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <strong class="font-bold">Error!</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                <div class="alert alert-success mb-1 rounded-0" role="alert">
                    <div class="alert-body">
                        {{ session('status') }}
                    </div>
                </div>
                @endif
                <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="login-email" class="form-label">{{ __('Email') }}</label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email"
                            name="email" placeholder="{{ __('Email') }}" autofocus value="{{ old('email') }}">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <span class="fw-medium">{{ $message }}</span>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-6 form-password-toggle">
                        <label class="form-label" for="login-password">{{ __('Password') }}</label>
                        <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                            <input type="password" id="login-password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <span class="fw-medium">{{ $message }}</span>
                        </span>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label for="assignment" class="form-label">{{ __('Rol - Departamento') }}</label>
                        <select id="assignment" name="assignment_id" required class="select2 form-select">
                            <option value="">-- Seleccione un Rol --</option>
                            @if(old('assignment_id'))
                                @foreach($assignments ?? [] as $assignment)
                                    <option value="{{ $assignment['id'] }}" {{ old('assignment_id') == $assignment['id'] ? 'selected' : '' }}>
                                        {{ $assignment['display'] }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('assignment_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @php
                    /*
                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label for="login-email" class="form-label">{{ __('Department') }}</label>
                        <select id="department" name="department" required class="select2 form-select">
                            <option value="">-- Seleccione un Departamento --</option>
                            @if(old('department'))
                                @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('department')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    */
                    @endphp

                    <div class="my-8">
                        <div class="d-flex justify-content-between">
                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                <p class="mb-0">{{ __('Forgot Password') }}</p>
                            </a>
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Sign in') }}</button>
                </form>
            </div>
        </div>
        <!-- /Login -->

        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
            <div class="w-100 d-flex justify-content-center">
                <img src="{{ asset('assets/img/illustrations/boy-with-rocket-' . $configData['style'] . '.png') }}"
                    class="img-fluid" alt="Login image" width="700"
                    data-app-dark-img="illustrations/boy-with-rocket-dark.png"
                    data-app-light-img="illustrations/boy-with-rocket-light.png">
            </div>
        </div>
        <!-- /Left Text -->
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAuthentication');
    const emailInput = document.getElementById('login-email');
    const assignmentSelect = document.getElementById('assignment'); // Usamos el ID correcto

    // Guardar el valor antiguo para restaurarlo después
    const oldAssignmentId = "{{ old('assignment_id') }}";

    // 1. Manejo del email - Cargar asignaciones automáticamente
    emailInput.addEventListener('input', debounce(function() {
        const email = this.value.trim();
        if (validateEmail(email)) {
            loadAssignments(email);
        }
    }, 800));

    // Función para cargar asignaciones
    function loadAssignments(email) {
        if (email.length > 3) {
            fetch(`/api/user-assignments?email=${encodeURIComponent(email)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta');
                    return response.json();
                })
                .then(data => {
                    // Limpiar select solo si existe
                    if (assignmentSelect) {
                        assignmentSelect.innerHTML = '<option value="">-- Seleccione una asignación --</option>';

                        data.forEach(assignment => {
                            const option = document.createElement('option');
                            option.value = assignment.id;
                            option.textContent = assignment.display;

                            // Restaurar selección anterior
                            if (oldAssignmentId && assignment.id == oldAssignmentId) {
                                option.selected = true;
                            }

                            assignmentSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }

    // Funciones auxiliares
    function debounce(fn, delay) {
        let timer;
        return function() {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, arguments), delay);
        };
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = 'Autenticando...';
    });
});



</script>
