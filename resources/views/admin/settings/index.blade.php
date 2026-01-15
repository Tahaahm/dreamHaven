@extends('layouts.admin-layout')

@section('title', 'System Settings')

@section('content')

<div class="max-w-[1600px] mx-auto animate-in fade-in zoom-in-95 duration-500 pb-20">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-10 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">System Settings</h1>
            <p class="text-slate-500 font-medium">Configure global platform parameters and preferences.</p>
        </div>
        <div>
            <button type="submit" form="settingsForm" class="bg-black hover:bg-slate-800 text-white px-8 py-3 text-sm font-bold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            {{-- Settings Navigation (Sidebar) --}}
            <div class="lg:col-span-1">
                <nav class="space-y-1 sticky top-6">
                    <a href="#general" class="flex items-center gap-3 px-4 py-3 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-bold text-indigo-600 ring-1 ring-indigo-50 transition">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center"><i class="fas fa-globe"></i></div>
                        General & Site
                    </a>
                    <a href="#real-estate" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-white hover:shadow-sm rounded-xl transition text-sm font-bold border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-home"></i></div>
                        Real Estate Logic
                    </a>
                    <a href="#finance" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-white hover:shadow-sm rounded-xl transition text-sm font-bold border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-coins"></i></div>
                        Finance & Currency
                    </a>
                    <a href="#security" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-white hover:shadow-sm rounded-xl transition text-sm font-bold border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fas fa-shield-alt"></i></div>
                        Security & Access
                    </a>
                </nav>
            </div>

            {{-- Settings Content --}}
            <div class="lg:col-span-3 space-y-10">

                {{-- 1. General Settings --}}
                <div id="general" class="scroll-mt-6">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h3 class="text-lg font-black text-slate-900 mb-1">General Information</h3>
                        <p class="text-xs text-slate-500 mb-6">Basic identity and support contact details.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="input-label">Platform Name</label>
                                <input type="text" name="site_name" value="Dream Haven" class="input-modern">
                            </div>
                            <div>
                                <label class="input-label">Support Email</label>
                                <input type="email" name="support_email" value="support@dreamhaven.com" class="input-modern">
                            </div>
                            <div>
                                <label class="input-label">Contact Phone</label>
                                <input type="text" name="support_phone" value="+964 750 000 0000" class="input-modern">
                            </div>
                            <div>
                                <label class="input-label">Default Language</label>
                                <select name="default_locale" class="input-modern cursor-pointer">
                                    <option value="en">English</option>
                                    <option value="ar">Arabic</option>
                                    <option value="ku">Kurdish</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Real Estate Logic --}}
                <div id="real-estate" class="scroll-mt-6">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h3 class="text-lg font-black text-slate-900 mb-1">Real Estate Configuration</h3>
                        <p class="text-xs text-slate-500 mb-6">Rules for property listings and agents.</p>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="input-label">Default Listing Duration (Days)</label>
                                    <input type="number" name="listing_duration" value="30" class="input-modern">
                                </div>
                                <div>
                                    <label class="input-label">Max Images per Property</label>
                                    <input type="number" name="max_images" value="15" class="input-modern">
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900">Auto-Approve Listings</span>
                                        <span class="text-[10px] text-slate-500">Properties go live immediately without review.</span>
                                    </span>
                                    <input type="checkbox" name="auto_approve_properties" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 relative"></div>
                                </label>

                                <label class="flex items-center justify-between cursor-pointer group">
                                    <span class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900">Agent Verification Required</span>
                                        <span class="text-[10px] text-slate-500">Agents must be verified to post.</span>
                                    </span>
                                    <input type="checkbox" name="require_agent_verification" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 relative"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Finance Settings --}}
                <div id="finance" class="scroll-mt-6">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h3 class="text-lg font-black text-slate-900 mb-1">Finance & Currency</h3>
                        <p class="text-xs text-slate-500 mb-6">Exchange rates and transaction rules.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="input-label">System Currency</label>
                                <select name="system_currency" class="input-modern cursor-pointer">
                                    <option value="USD" selected>USD ($)</option>
                                    <option value="IQD">IQD</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Exchange Rate (1 USD = ? IQD)</label>
                                <input type="number" name="exchange_rate" value="1320" class="input-modern font-mono font-bold text-emerald-600">
                            </div>
                            <div>
                                <label class="input-label">Default Commission (%)</label>
                                <input type="number" name="default_commission" value="2.5" step="0.1" class="input-modern">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Security & System --}}
                <div id="security" class="scroll-mt-6">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h3 class="text-lg font-black text-slate-900 mb-1">Security & System</h3>
                        <p class="text-xs text-slate-500 mb-6">Access control and maintenance.</p>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 border border-slate-100 rounded-xl">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Maintenance Mode</p>
                                    <p class="text-[10px] text-slate-500">Disable public access to the site.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_mode" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-4 border border-slate-100 rounded-xl">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Allow New Registrations</p>
                                    <p class="text-[10px] text-slate-500">Users can sign up via the app.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_registrations" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
    .input-label {
        @apply block text-[11px] font-bold text-slate-400 uppercase mb-1.5 tracking-wide;
    }
    .input-modern {
        @apply w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:border-black focus:ring-0 transition-all duration-200 outline-none;
    }
</style>

@endsection
