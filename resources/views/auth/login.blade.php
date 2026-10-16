@extends('layouts.auth')

@section('title', 'Masuk Sistem')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">

    <!-- Corporate Brand Identity -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-800 via-brand-700 to-blue-500 text-white shadow-xl shadow-brand-950/50 mb-2 border border-blue-400/30">
            <!-- Maritime Anchor / Helm SVG -->
            <svg class="w-8 h-8 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20m0 0a8 8 0 01-8-8H2m10 8a8 8 0 008-8h-2M12 2a3 3 0 100 6 3 3 0 000-6z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-white tracking-tight">
            IKI IT Helpdesk
        </h2>
        <p class="text-xs text-blue-200/80 font-semibold tracking-wider uppercase">
            PT Industri Kapal Indonesia (Persero)
        </p>
    </div>

    <!-- Login Card -->
    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white/95 backdrop-blur-md py-8 px-6 shadow-2xl rounded-3xl sm:px-10 border border-slate-100/90 space-y-6">

            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Masuk ke Akun Anda</h3>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan akun korporat untuk mengakses sistem pelayanan IT</p>
            </div>

            @if(session('error'))
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center space-x-2.5 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Email Korporat
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                            </svg>
                        </div>
                        <input id="email"
                               name="email"
                               type="email"
                               autocomplete="email"
                               required
                               value="{{ old('email') }}"
                               placeholder="nama@iki.co.id"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password"
                               name="password"
                               type="password"
                               autocomplete="current-password"
                               required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400">
                        <button type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 cursor-pointer">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-brand-700 via-brand-800 to-blue-800 hover:from-brand-600 hover:to-blue-700 text-white font-bold text-sm shadow-md shadow-brand-950/20 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600">
                        Masuk Sistem
                    </button>
                </div>
            </form>

            <!-- Quick Demo Accounts for Testing / Review -->
            <div class="pt-5 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-500 mb-2.5 uppercase tracking-wider text-center">
                    Akses Cepat Pengujian Demo (Password: <span class="font-mono text-slate-700">password123</span>)
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button"
                            onclick="fillCredentials('employee@iki.co.id', 'password123')"
                            class="p-2.5 text-left rounded-xl bg-slate-50 hover:bg-blue-50/80 border border-slate-200 hover:border-blue-300 transition-all duration-200 cursor-pointer group shadow-2xs">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Karyawan</span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate font-mono mt-0.5">employee@iki.co.id</div>
                    </button>
                    <button type="button"
                            onclick="fillCredentials('agent@iki.co.id', 'password123')"
                            class="p-2.5 text-left rounded-xl bg-slate-50 hover:bg-blue-50/80 border border-slate-200 hover:border-blue-300 transition-all duration-200 cursor-pointer group shadow-2xs">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>IT Agent</span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate font-mono mt-0.5">agent@iki.co.id</div>
                    </button>
                    <button type="button"
                            onclick="fillCredentials('supervisor@iki.co.id', 'password123')"
                            class="p-2.5 text-left rounded-xl bg-slate-50 hover:bg-blue-50/80 border border-slate-200 hover:border-blue-300 transition-all duration-200 cursor-pointer group shadow-2xs">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Supervisor</span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate font-mono mt-0.5">supervisor@iki.co.id</div>
                    </button>
                    <button type="button"
                            onclick="fillCredentials('admin@iki.co.id', 'password123')"
                            class="p-2.5 text-left rounded-xl bg-slate-50 hover:bg-blue-50/80 border border-slate-200 hover:border-blue-300 transition-all duration-200 cursor-pointer group shadow-2xs">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Super Admin</span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate font-mono mt-0.5">admin@iki.co.id</div>
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            Butuh bantuan akses akun? Hubungi Tim Dukungan IT IKI di ext. 104
        </p>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    }
</script>
@endsection
