<script setup>
import { Link } from '@inertiajs/vue3';
import ToastNotification from '../Components/Common/ToastNotification.vue';
</script>

<template>
    <div class="h-screen overflow-hidden flex selection:bg-blue-500 selection:text-white">
        <!-- Global Toast Notification Container -->
        <ToastNotification />

        <!-- ═══════════════════════════════════════════════ -->
        <!-- LEFT PANEL — Brand / Hero (hidden on mobile)   -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-[52%] xl:w-[55%] relative flex-col justify-between p-10 xl:p-14 overflow-hidden"
             style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #172554 100%);"><div class="absolute inset-0 pointer-events-none" style="background-size:36px 36px;background-image:linear-gradient(to right,rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.05) 1px,transparent 1px);" />

            <!-- Ambient spheres -->
            <div class="absolute -top-24 -left-24 w-[500px] h-[500px] rounded-full blur-2xl pointer-events-none" style="background:radial-gradient(circle,rgba(96,165,250,.3) 0%,rgba(37,99,235,.05) 70%,transparent 100%);" />
            <div class="absolute -bottom-24 right-0 w-[550px] h-[550px] rounded-full blur-3xl pointer-events-none" style="background:radial-gradient(circle,rgba(79,70,229,.4) 0%,rgba(30,58,138,.08) 75%,transparent 100%);" />

            <!-- TOP: Logo -->
            <header class="relative z-10 flex items-center gap-3.5">
                <Link href="/" class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center ring-1 ring-white/25 shadow-inner" style="background:rgba(255,255,255,.12);backdrop-filter:blur(12px);">
                        <img src="/assets/logo.png" alt="Partner Portal" class="w-6 h-6 object-contain" />
                    </div>
                    <div>
                        <div class="text-lg font-bold tracking-tight leading-tight text-white">Partner Portal</div>
                        <div class="text-xs font-medium text-blue-200/90 tracking-wide">Management &amp; Distribution</div>
                    </div>
                </Link>
            </header>

            <!-- MIDDLE: Hero -->
            <div class="relative z-10 max-w-xl py-4">
                <h1 class="text-4xl xl:text-5xl font-extrabold leading-[1.12] tracking-tight text-white drop-shadow-sm">
                    Grow your <span style="color:#bfdbfe;">partner network</span> with confidence.
                </h1>
                <p class="mt-5 text-base text-blue-100/90 leading-relaxed max-w-lg">
                    Manage partners, track referrals, monitor subscriptions, and drive revenue — all from one powerful dashboard.
                </p>

                <!-- Glass Feature Pills -->
                <div class="mt-8 space-y-3">
                    <div class="flex items-center gap-3.5 py-2.5 px-4 rounded-xl max-w-md transition-all hover:bg-white/10" style="background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.16);">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-sm font-medium tracking-wide text-white/95">Multi-tier partner management</span>
                    </div>
                    <div class="flex items-center gap-3.5 py-2.5 px-4 rounded-xl max-w-md transition-all hover:bg-white/10" style="background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.16);">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <span class="text-sm font-medium tracking-wide text-white/95">Real-time sales &amp; commission reports</span>
                    </div>
                    <div class="flex items-center gap-3.5 py-2.5 px-4 rounded-xl max-w-md transition-all hover:bg-white/10" style="background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.16);">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        </div>
                        <span class="text-sm font-medium tracking-wide text-white/95">Referral tracking &amp; lead management</span>
                    </div>
                </div>
            </div>

            <!-- BOTTOM: Trust & Highlights Footer -->
            <footer class="relative z-10 pt-6 border-t border-white/15 max-w-xl">
                <div class="grid grid-cols-3 gap-6 items-center">
                    <!-- Rating & Avatars -->
                    <div class="flex items-center gap-2.5">
                        <div class="flex -space-x-2 shrink-0">
                            <div class="w-7 h-7 rounded-full bg-blue-400/30 border-2 border-blue-700 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">JD</div>
                            <div class="w-7 h-7 rounded-full bg-indigo-400/30 border-2 border-blue-700 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">AR</div>
                            <div class="w-7 h-7 rounded-full bg-sky-400/30 border-2 border-blue-700 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">MK</div>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-amber-300 text-[11px] font-medium leading-none">
                                <span>★★★★★</span>
                                <span class="text-white font-bold text-xs ml-0.5">4.9</span>
                            </div>
                            <p class="text-[11px] text-blue-200/80 mt-1">500+ Partners</p>
                        </div>
                    </div>

                    <!-- SLA Uptime -->
                    <div class="border-l border-white/15 pl-6">
                        <div class="text-xs font-bold text-white tracking-tight">99.9% Uptime</div>
                        <p class="text-[11px] text-blue-200/80 mt-0.5">Enterprise SLA</p>
                    </div>

                    <!-- Automated Payouts -->
                    <div class="border-l border-white/15 pl-6">
                        <div class="text-xs font-bold text-white tracking-tight">Instant Payouts</div>
                        <p class="text-[11px] text-blue-200/80 mt-0.5">Auto Tracking</p>
                    </div>
                </div>
            </footer>

        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- RIGHT PANEL — Auth Form                        -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col h-screen overflow-y-auto bg-white">

            <!-- Mobile top bar (only visible on small screens) -->
            <div class="lg:hidden flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <Link href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <img src="/assets/logo.png" alt="Partner Portal" class="w-5 h-5 object-contain" />
                    </div>
                    <span class="font-bold text-gray-900 text-sm">Partner Portal</span>
                </Link>
                <span class="text-xs text-gray-400">Secure Access</span>
            </div>

            <!-- Form Area -->
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-6 sm:px-10 lg:px-16 xl:px-20">
                <div class="w-full max-w-sm xl:max-w-md">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-5 text-center text-xs text-gray-400 border-t border-gray-100 lg:hidden">
                &copy; {{ new Date().getFullYear() }} Partner Portal. All rights reserved.
            </div>
            <div class="hidden lg:block py-6 text-xs text-gray-400 text-center">
                &copy; {{ new Date().getFullYear() }} Partner Portal. All rights reserved.
            </div>
        </div>

    </div>
</template>
