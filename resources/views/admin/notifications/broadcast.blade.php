@extends('layouts.admin-layout')

@section('title', 'Broadcast Notification')

@section('content')
<div class="max-w-7xl mx-auto animate-fade-in-up">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Broadcast Notification</h1>
            <p class="text-slate-500 mt-2 text-sm font-medium">Send real-time push notifications to users, agents, and offices across the platform.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg shadow-sm hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Success Stats Panel (Hidden by default, shown via JS) --}}
    <div id="successPanel" class="hidden mb-8 animate-fade-in-up">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-4 shadow-sm mb-4">
            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center shrink-0 mt-0.5 shadow-sm border border-emerald-200">
                <i class="fas fa-check-double text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-emerald-900 text-base">Broadcast Successfully Dispatched</h4>
                <p class="text-sm font-medium text-emerald-700 mt-1">The notification payload has been sent to the Firebase Cloud Messaging queue and internal database.</p>
            </div>
            <button type="button" onclick="resetFormState()" class="text-emerald-500 hover:text-emerald-700 p-1 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Dynamic Reach Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 bg-white rounded-xl border border-slate-200 shadow-sm divide-y sm:divide-y-0 sm:divide-x divide-slate-100 overflow-hidden">
            <div class="p-5 bg-slate-50/50">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Delivered</p>
                <p id="stat-total" class="text-3xl font-black text-[#303b97]">0</p>
            </div>
            <div class="p-5 hover:bg-slate-50/50 transition-colors">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Users</p>
                <p id="stat-users" class="text-3xl font-black text-slate-900">0</p>
            </div>
            <div class="p-5 hover:bg-slate-50/50 transition-colors">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Agents</p>
                <p id="stat-agents" class="text-3xl font-black text-slate-900">0</p>
            </div>
            <div class="p-5 hover:bg-slate-50/50 transition-colors">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Offices</p>
                <p id="stat-offices" class="text-3xl font-black text-slate-900">0</p>
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    <div id="errorAlert" class="hidden mb-6 bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <div class="w-8 h-8 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center shrink-0">
            <i class="fas fa-exclamation"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-rose-900 text-sm">Transmission Failed</h4>
            <p id="errorText" class="text-sm font-medium text-rose-700">An error occurred while sending the broadcast.</p>
        </div>
    </div>

    {{-- Broadcast Form --}}
    <form id="broadcastForm" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">

        {{-- Section: Multilingual Content --}}
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center gap-2">
            <i class="fas fa-language text-[#303b97] text-lg"></i>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Multilingual Content</h2>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- English --}}
            <div class="space-y-4 relative group">
                <div class="absolute -inset-2 bg-slate-50 rounded-xl z-0 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">English</label>
                        <span class="text-[10px] font-bold bg-[#303b97]/10 text-[#303b97] px-2 py-0.5 rounded border border-[#303b97]/20">EN (Required)</span>
                    </div>
                    <div>
                        <input type="text" name="title_en" placeholder="Notification Title" required
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm">
                    </div>
                    <div class="mt-4">
                        <textarea name="message_en" rows="4" placeholder="Notification body message..." required
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Arabic --}}
            <div class="space-y-4 relative group" dir="rtl">
                <div class="absolute -inset-2 bg-slate-50 rounded-xl z-0 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3" dir="ltr">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Arabic</label>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded border border-slate-200">AR (Optional)</span>
                    </div>
                    <div>
                        <input type="text" name="title_ar" placeholder="عنوان الإشعار"
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm text-right">
                    </div>
                    <div class="mt-4">
                        <textarea name="message_ar" rows="4" placeholder="محتوى رسالة الإشعار..."
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm text-right resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Kurdish --}}
            <div class="space-y-4 relative group" dir="rtl">
                <div class="absolute -inset-2 bg-slate-50 rounded-xl z-0 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3" dir="ltr">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kurdish</label>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded border border-slate-200">KU (Optional)</span>
                    </div>
                    <div>
                        <input type="text" name="title_ku" placeholder="سەردێڕی ئاگادارکردنەوە"
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm text-right">
                    </div>
                    <div class="mt-4">
                        <textarea name="message_ku" rows="4" placeholder="پەیامی ئاگادارکردنەوە..."
                            class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm text-right resize-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Configuration --}}
        <div class="bg-slate-50 border-y border-slate-200 px-6 py-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-[#303b97] text-lg"></i>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Delivery Configuration</h2>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Target Audience</label>
                <div class="relative">
                    <select name="recipient_type" required class="appearance-none block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm cursor-pointer pr-10">
                        <option value="all">Everyone (Global Broadcast)</option>
                        <option value="users">Standard Users Only</option>
                        <option value="agents">Verified Agents Only</option>
                        <option value="offices">Real Estate Offices Only</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Notification Category</label>
                <div class="relative">
                    <select name="type" required class="appearance-none block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm cursor-pointer pr-10">
                        <option value="system">System Announcement</option>
                        <option value="promotion">Marketing / Promotion</option>
                        <option value="alert">Important Alert</option>
                        <option value="property">Property Related Update</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Importance Priority</label>
                <div class="relative">
                    <select name="priority" required class="appearance-none block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm cursor-pointer pr-10">
                        <option value="medium">Medium (Standard)</option>
                        <option value="low">Low (Silent Push)</option>
                        <option value="high">High (Immediate)</option>
                        <option value="urgent">Urgent (Bypass DND)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Action Link --}}
        <div class="bg-slate-50 border-y border-slate-200 px-6 py-4 flex items-center gap-2">
            <i class="fas fa-link text-[#303b97] text-lg"></i>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Deep Link Routing (Optional)</h2>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/30">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">In-App Action Route</label>
                <input type="text" name="action_url" placeholder="e.g., /properties/view/1024"
                    class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm">
                <p class="text-[11px] font-medium text-slate-500 mt-1.5"><i class="fas fa-info-circle"></i> Directs the user to this screen when tapped.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Action Button Label</label>
                <input type="text" name="action_text" placeholder="e.g., View Deal"
                    class="block w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#303b97]/20 focus:border-[#303b97] transition-all shadow-sm">
                <p class="text-[11px] font-medium text-slate-500 mt-1.5"><i class="fas fa-info-circle"></i> Text displayed on the notification action button.</p>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="px-6 py-4 bg-white border-t border-slate-200 flex items-center justify-between">
            <button type="button" onclick="document.getElementById('broadcastForm').reset()" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
                Reset Fields
            </button>
            <button type="submit" id="submitBtn" class="bg-[#303b97] hover:bg-[#232c75] text-white px-8 py-3 rounded-lg text-sm font-bold transition-all shadow-lg hover:shadow-xl flex items-center gap-2 transform active:scale-95">
                <i class="fas fa-paper-plane"></i> Dispatch Broadcast
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // Animate numbers counting up
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            // Easing out function
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            obj.innerHTML = Math.floor(easeOutQuart * (end - start) + start).toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    function resetFormState() {
        document.getElementById('successPanel').classList.add('hidden');
        document.getElementById('errorAlert').classList.add('hidden');
        document.getElementById('broadcastForm').reset();
    }

    document.getElementById('broadcastForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const originalBtnContent = submitBtn.innerHTML;
        const errorAlert = document.getElementById('errorAlert');
        const successPanel = document.getElementById('successPanel');

        // Loading State
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

        // Hide previous alerts
        errorAlert.classList.add('hidden');
        successPanel.classList.add('hidden');

        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch("{{ route('admin.notifications.broadcast') ?? '/admin/notifications/broadcast' }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && (result.status === true || result.code === 200)) {
                // Fetch data from response
                const data = result.data || {};
                const total = parseInt(data.sent_to) || 0;
                const users = parseInt(data.users) || 0;
                const agents = parseInt(data.agents) || 0;
                const offices = parseInt(data.offices) || 0;

                // Display Panel & Scroll to top
                successPanel.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Animate Statistics
                animateValue(document.getElementById('stat-total'), 0, total, 1500);
                animateValue(document.getElementById('stat-users'), 0, users, 1500);
                animateValue(document.getElementById('stat-agents'), 0, agents, 1500);
                animateValue(document.getElementById('stat-offices'), 0, offices, 1500);

            } else {
                // Validation or Logic Error
                errorAlert.classList.remove('hidden');
                let errorMsg = result.message || 'Transmission failed due to an unknown server error.';

                if (result.errors) {
                    const firstError = Object.values(result.errors)[0];
                    errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
                }

                document.getElementById('errorText').innerText = errorMsg;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            errorAlert.classList.remove('hidden');
            document.getElementById('errorText').innerText = 'Network error: Unable to connect to the server.';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            // Restore button state
            submitBtn.innerHTML = originalBtnContent;
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    });
</script>
@endpush
