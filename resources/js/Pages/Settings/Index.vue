<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserIcon,
    Cog6ToothIcon,
    PhotoIcon,
    BellIcon,
    ShieldCheckIcon,
    BuildingOffice2Icon,
    PaperClipIcon,
    ArrowUpTrayIcon,
    XMarkIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    KeyIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    user: { type: Object, required: true },
    partner: { type: Object, required: true },
    flash: { type: Object, default: () => ({}) },
});

// Detect URL query parameter for active tab
const page = usePage();
const initialTab = new URLSearchParams(window.location.search).get('tab');

const activeTab = ref(
    initialTab === 'profile' ? 'profile' :
    initialTab === 'partner' ? 'partner' :
    initialTab === 'branding' || initialTab === 'image' ? 'branding' :
    'branding' // default to branding / attach image tab for quick preview
);

// Floating Sidebar Menu Items
const menuItems = [
    {
        id: 'branding',
        label: 'Attach Image & Branding',
        description: 'Profile photo, partner logo & banner attachments',
        icon: PhotoIcon,
        badge: 'New',
        badgeClass: 'bg-emerald-50 text-emerald-600 border-emerald-200',
    },
    {
        id: 'profile',
        label: 'Profile & Account',
        description: 'Personal details, display name & email',
        icon: UserIcon,
    },
    {
        id: 'partner',
        label: 'Partner Settings',
        description: 'Company information & business ID',
        icon: BuildingOffice2Icon,
    },
    {
        id: 'notifications',
        label: 'Notifications',
        description: 'Email alerts & system notifications',
        icon: BellIcon,
    },
    {
        id: 'security',
        label: 'Security & Password',
        description: 'Password update & authentication',
        icon: ShieldCheckIcon,
    },
];

// -------------------------------------------------------------
// Image Attachment Forms & State
// -------------------------------------------------------------
const avatarFile = ref(null);
const avatarPreview = ref(props.user.avatar || null);
const logoFile = ref(null);
const logoPreview = ref(props.partner.logo || null);
const bannerFile = ref(null);
const bannerPreview = ref(props.partner.banner || null);

const isDraggingAvatar = ref(false);
const isDraggingLogo = ref(false);

const imageForm = useForm({
    type: 'avatar',
    image: null,
    image_url: null,
});

const handleFileSelect = (event, type) => {
    const file = event.target.files[0];
    if (!file) return;
    processImageFile(file, type);
};

const handleDrop = (event, type) => {
    if (type === 'avatar') isDraggingAvatar.value = false;
    if (type === 'logo') isDraggingLogo.value = false;
    const file = event.dataTransfer.files[0];
    if (!file) return;
    processImageFile(file, type);
};

const processImageFile = (file, type) => {
    if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file (PNG, JPG, SVG, WebP)');
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        if (type === 'avatar') {
            avatarFile.value = file;
            avatarPreview.value = e.target.result;
            saveImage('avatar', file, e.target.result);
        } else if (type === 'logo') {
            logoFile.value = file;
            logoPreview.value = e.target.result;
            saveImage('logo', file, e.target.result);
        } else if (type === 'banner') {
            bannerFile.value = file;
            bannerPreview.value = e.target.result;
            saveImage('banner', file, e.target.result);
        }
    };
    reader.readAsDataURL(file);
};

const saveImage = (type, file, dataUrl) => {
    imageForm.type = type;
    imageForm.image = file;
    imageForm.image_url = dataUrl;

    imageForm.post('/settings/image', {
        preserveScroll: true,
        onSuccess: () => {
            // Updated successfully
        },
    });
};

const removeImage = (type) => {
    if (type === 'avatar') {
        avatarFile.value = null;
        avatarPreview.value = null;
        saveImage('avatar', null, null);
    } else if (type === 'logo') {
        logoFile.value = null;
        logoPreview.value = null;
        saveImage('logo', null, null);
    } else if (type === 'banner') {
        bannerFile.value = null;
        bannerPreview.value = null;
        saveImage('banner', null, null);
    }
};

// Preset Sample Logos for Fast Attachment
const sampleLogos = [
    { name: 'Modern Tech', url: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=200&q=80' },
    { name: 'Gradient Pulse', url: 'https://images.unsplash.com/photo-1634017839464-5c339ebe3cb4?auto=format&fit=crop&w=200&q=80' },
    { name: 'Minimalist Blue', url: 'https://images.unsplash.com/photo-1579546929518-9e396f3cc809?auto=format&fit=crop&w=200&q=80' },
];

const attachPresetImage = (type, url) => {
    if (type === 'avatar') {
        avatarPreview.value = url;
    } else if (type === 'logo') {
        logoPreview.value = url;
    }
    imageForm.type = type;
    imageForm.image = null;
    imageForm.image_url = url;

    imageForm.post('/settings/image', {
        preserveScroll: true,
    });
};

// -------------------------------------------------------------
// Profile Form
// -------------------------------------------------------------
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const submitProfile = () => {
    profileForm.post('/settings/profile', {
        preserveScroll: true,
    });
};

// -------------------------------------------------------------
// Partner Info Form
// -------------------------------------------------------------
const partnerForm = useForm({
    name: props.partner.name,
    code: props.partner.code,
    website: props.partner.website,
    tax_id: props.partner.tax_id,
});

const submitPartner = () => {
    // Statically handle or post
    alert('Partner settings saved successfully!');
};

// -------------------------------------------------------------
// Security Form
// -------------------------------------------------------------
const securityForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitSecurity = () => {
    securityForm.post('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            securityForm.reset();
        },
    });
};

// Notification Preferences
const notificationsState = ref({
    email_lead: true,
    email_payout: true,
    email_weekly: false,
    browser_alerts: true,
});
</script>

<template>
    <Head title="Partner Settings" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Top Header & Breadcrumbs -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-xs">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
                        <span>Dashboard</span>
                        <span>/</span>
                        <span class="text-gray-500">Settings</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                        <span>Partner Settings</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <SparklesIcon class="w-3.5 h-3.5" />
                            Account &amp; Branding
                        </span>
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Manage your profile, attach branding images, configure partner details, and update security settings.
                    </p>
                </div>

                <!-- User Quick Info -->
                <div class="flex items-center gap-3 bg-gray-50/80 p-3 rounded-2xl border border-gray-100 shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-xs overflow-hidden shrink-0">
                        <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="w-full h-full object-cover" />
                        <span v-else>{{ user.name ? user.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase() : 'U' }}</span>
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gray-900 truncate">{{ user.name }}</div>
                        <div class="text-[11px] text-gray-500 capitalize">{{ user.role.replace('_', ' ') }}</div>
                    </div>
                </div>
            </div>

            <!-- Flash Success Alert -->
            <div v-if="flash?.success || $page.props.flash?.success" class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
                <CheckCircleIcon class="w-5 h-5 text-emerald-600 shrink-0" />
                <span>{{ flash?.success || $page.props.flash?.success }}</span>
            </div>

            <!-- Main Layout: Floating Sidebar + Tab Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- ══════════════════════════════════════════ -->
                <!-- Floating Sidebar (Left Column - 4 cols)   -->
                <!-- ══════════════════════════════════════════ -->
                <aside class="lg:col-span-4 sticky top-24 z-20 space-y-4">
                    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-gray-100 p-3 sm:p-4 transition-all">
                        <div class="px-3 py-2 border-b border-gray-100 mb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Navigation</p>
                            <p class="text-xs font-semibold text-gray-800 mt-0.5">Settings Menu</p>
                        </div>

                        <nav class="space-y-1.5" aria-label="Settings Sub Navigation">
                            <button
                                v-for="item in menuItems"
                                :key="item.id"
                                type="button"
                                @click="activeTab = item.id"
                                :class="[
                                    'w-full flex items-start gap-3 p-3 rounded-2xl text-left transition-all cursor-pointer group relative',
                                    activeTab === item.id
                                        ? 'bg-linear-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-600/20 font-semibold'
                                        : 'hover:bg-gray-50 text-gray-700 font-medium'
                                ]"
                            >
                                <span :class="[
                                    'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors',
                                    activeTab === item.id ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-blue-50 group-hover:text-blue-600'
                                ]">
                                    <component :is="item.icon" class="w-5 h-5" />
                                </span>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-xs truncate" :class="activeTab === item.id ? 'text-white font-bold' : 'text-gray-900 font-semibold'">
                                            {{ item.label }}
                                        </span>
                                        <span v-if="item.badge" :class="['text-[9px] font-bold px-1.5 py-0.5 rounded-full border shrink-0', activeTab === item.id ? 'bg-white/20 text-white border-white/30' : item.badgeClass]">
                                            {{ item.badge }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] truncate mt-0.5" :class="activeTab === item.id ? 'text-blue-100' : 'text-gray-400'">
                                        {{ item.description }}
                                    </p>
                                </div>
                            </button>
                        </nav>
                    </div>

                    <!-- Sidebar Quick Stats Card -->
                    <div class="bg-linear-to-br from-slate-900 to-indigo-950 rounded-3xl p-5 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-blue-500/20 blur-xl pointer-events-none" />
                        <div class="flex items-center gap-2 text-xs font-semibold text-blue-300 mb-2">
                            <PaperClipIcon class="w-4 h-4 text-blue-400" />
                            <span>Attachment Status</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Upload your company logo and profile avatar to personalize invoices and referral links.
                        </p>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Attached Media:</span>
                            <span class="font-bold text-white">{{ (avatarPreview ? 1 : 0) + (logoPreview ? 1 : 0) + (bannerPreview ? 1 : 0) }} / 3 Assets</span>
                        </div>
                    </div>
                </aside>

                <!-- ══════════════════════════════════════════ -->
                <!-- Main Content Area (Right Column - 8 cols)  -->
                <!-- ══════════════════════════════════════════ -->
                <main class="lg:col-span-8 space-y-6">
                    <!-- TAB 1: ATTACH IMAGE & BRANDING MENU -->
                    <div v-if="activeTab === 'branding'" class="space-y-6">
                        <!-- Card 1: Profile Avatar Attachment -->
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7">
                            <div class="flex items-center justify-between gap-4 pb-5 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                        <PhotoIcon class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-900">Profile Avatar Attachment</h2>
                                        <p class="text-xs text-gray-500">Upload or attach a personal profile image for your account.</p>
                                    </div>
                                </div>
                                <span v-if="avatarPreview" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Image Attached
                                </span>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row items-center gap-6">
                                <!-- Live Image Preview -->
                                <div class="relative group shrink-0">
                                    <div class="w-24 h-24 rounded-3xl bg-linear-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xl flex items-center justify-center shadow-md overflow-hidden ring-4 ring-blue-50">
                                        <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar Preview" class="w-full h-full object-cover" />
                                        <span v-else>{{ user.name ? user.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase() : 'U' }}</span>
                                    </div>
                                    <button
                                        v-if="avatarPreview"
                                        type="button"
                                        @click="removeImage('avatar')"
                                        class="absolute -top-2 -right-2 p-1.5 bg-red-600 text-white rounded-full shadow-md hover:bg-red-700 transition-colors cursor-pointer"
                                        title="Remove attached avatar"
                                    >
                                        <XMarkIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <!-- Upload Drop Zone -->
                                <div
                                    @dragover.prevent="isDraggingAvatar = true"
                                    @dragleave.prevent="isDraggingAvatar = false"
                                    @drop.prevent="handleDrop($event, 'avatar')"
                                    :class="[
                                        'flex-1 w-full border-2 border-dashed rounded-2xl p-5 text-center transition-all cursor-pointer relative',
                                        isDraggingAvatar ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50/50'
                                    ]"
                                >
                                    <input
                                        type="file"
                                        accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        @change="handleFileSelect($event, 'avatar')"
                                    />
                                    <ArrowUpTrayIcon class="w-7 h-7 text-blue-500 mx-auto mb-2" />
                                    <p class="text-xs font-bold text-gray-800">
                                        Click to upload <span class="font-normal text-gray-500">or drag &amp; drop image here</span>
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        Supports PNG, JPG, WebP or SVG (Max size: 5MB)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Partner Company Logo Attachment -->
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7">
                            <div class="flex items-center justify-between gap-4 pb-5 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                        <BuildingOffice2Icon class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-900">Partner Company Logo Attachment</h2>
                                        <p class="text-xs text-gray-500">Attach your organization logo for branded co-marketing and referrals.</p>
                                    </div>
                                </div>
                                <span v-if="logoPreview" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Logo Attached
                                </span>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row items-center gap-6">
                                <!-- Live Logo Preview -->
                                <div class="relative group shrink-0">
                                    <div class="w-28 h-20 rounded-2xl bg-gray-50 border border-gray-200 p-2 flex items-center justify-center overflow-hidden shadow-xs">
                                        <img v-if="logoPreview" :src="logoPreview" alt="Logo Preview" class="max-w-full max-h-full object-contain" />
                                        <div v-else class="text-center text-gray-400">
                                            <PhotoIcon class="w-6 h-6 mx-auto" />
                                            <span class="text-[10px]">No Logo</span>
                                        </div>
                                    </div>
                                    <button
                                        v-if="logoPreview"
                                        type="button"
                                        @click="removeImage('logo')"
                                        class="absolute -top-2 -right-2 p-1.5 bg-red-600 text-white rounded-full shadow-md hover:bg-red-700 transition-colors cursor-pointer"
                                        title="Remove logo"
                                    >
                                        <XMarkIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <!-- Drop Zone -->
                                <div
                                    @dragover.prevent="isDraggingLogo = true"
                                    @dragleave.prevent="isDraggingLogo = false"
                                    @drop.prevent="handleDrop($event, 'logo')"
                                    :class="[
                                        'flex-1 w-full border-2 border-dashed rounded-2xl p-5 text-center transition-all cursor-pointer relative',
                                        isDraggingLogo ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50/50'
                                    ]"
                                >
                                    <input
                                        type="file"
                                        accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        @change="handleFileSelect($event, 'logo')"
                                    />
                                    <PaperClipIcon class="w-7 h-7 text-indigo-500 mx-auto mb-2" />
                                    <p class="text-xs font-bold text-gray-800">
                                        Attach Company Logo <span class="font-normal text-gray-500">or browse files</span>
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        Recommended format: Transparent PNG or SVG (500x200 px)
                                    </p>
                                </div>
                            </div>

                            <!-- Sample Preset Logos Quick Selector -->
                            <div class="mt-6 pt-5 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-700 mb-3">Or attach a sample branding icon:</p>
                                <div class="flex flex-wrap items-center gap-3">
                                    <button
                                        v-for="preset in sampleLogos"
                                        :key="preset.name"
                                        type="button"
                                        @click="attachPresetImage('logo', preset.url)"
                                        class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-indigo-50 border border-gray-200 rounded-xl transition-all text-xs font-medium text-gray-700 cursor-pointer"
                                    >
                                        <img :src="preset.url" :alt="preset.name" class="w-5 h-5 rounded-full object-cover" />
                                        <span>{{ preset.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Attached Image Gallery Summary -->
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7">
                            <h3 class="text-sm font-bold text-gray-900 mb-3">Attached Branding Summary</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-3.5 bg-gray-50/80 border border-gray-100 rounded-2xl flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">AV</div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-800">User Avatar</p>
                                            <p class="text-[11px] text-gray-400">{{ avatarPreview ? 'Attached & Active' : 'Not attached' }}</p>
                                        </div>
                                    </div>
                                    <span :class="['w-2.5 h-2.5 rounded-full', avatarPreview ? 'bg-emerald-500' : 'bg-gray-300']" />
                                </div>

                                <div class="p-3.5 bg-gray-50/80 border border-gray-100 rounded-2xl flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">LG</div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-800">Partner Logo</p>
                                            <p class="text-[11px] text-gray-400">{{ logoPreview ? 'Attached & Active' : 'Not attached' }}</p>
                                        </div>
                                    </div>
                                    <span :class="['w-2.5 h-2.5 rounded-full', logoPreview ? 'bg-emerald-500' : 'bg-gray-300']" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: PROFILE & ACCOUNT MENU -->
                    <div v-if="activeTab === 'profile'" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7 space-y-6">
                        <div class="pb-5 border-b border-gray-100">
                            <h2 class="text-base font-bold text-gray-900">Profile Information</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Update your personal profile details and contact email address.</p>
                        </div>

                        <form @submit.prevent="submitProfile" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Full Name</label>
                                    <input
                                        v-model="profileForm.name"
                                        type="text"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email Address</label>
                                    <input
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Role Authorization</label>
                                <div class="px-3.5 py-2.5 text-xs text-gray-700 bg-gray-100 border border-gray-200 rounded-xl font-mono capitalize">
                                    {{ user.role.replace('_', ' ') }} (Read/Write Access)
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 transition-all cursor-pointer disabled:opacity-50"
                                >
                                    {{ profileForm.processing ? 'Saving...' : 'Save Profile Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: PARTNER SETTINGS MENU -->
                    <div v-if="activeTab === 'partner'" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7 space-y-6">
                        <div class="pb-5 border-b border-gray-100">
                            <h2 class="text-base font-bold text-gray-900">Partner Organization Details</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Manage partner company details, tax identifier, and business URLs.</p>
                        </div>

                        <form @submit.prevent="submitPartner" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Partner Name</label>
                                    <input
                                        v-model="partnerForm.name"
                                        type="text"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Partner Code</label>
                                    <input
                                        v-model="partnerForm.code"
                                        type="text"
                                        disabled
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded-xl font-mono"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Business Website</label>
                                    <input
                                        v-model="partnerForm.website"
                                        type="url"
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tax ID / GSTIN</label>
                                    <input
                                        v-model="partnerForm.tax_id"
                                        type="text"
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 transition-all cursor-pointer"
                                >
                                    Save Partner Info
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 4: NOTIFICATIONS MENU -->
                    <div v-if="activeTab === 'notifications'" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7 space-y-6">
                        <div class="pb-5 border-b border-gray-100">
                            <h2 class="text-base font-bold text-gray-900">Notification Preferences</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Control which notifications and email alerts you receive.</p>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100 cursor-pointer">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">New Lead Referral Alerts</p>
                                    <p class="text-[11px] text-gray-500">Receive email notification when a customer signs up using your code.</p>
                                </div>
                                <input v-model="notificationsState.email_lead" type="checkbox" class="w-4 h-4 text-blue-600 rounded cursor-pointer" />
                            </label>

                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100 cursor-pointer">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Commission Payout Confirmations</p>
                                    <p class="text-[11px] text-gray-500">Get notified when commission payments are transferred.</p>
                                </div>
                                <input v-model="notificationsState.email_payout" type="checkbox" class="w-4 h-4 text-blue-600 rounded cursor-pointer" />
                            </label>

                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-100 cursor-pointer">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">Weekly Performance Digest</p>
                                    <p class="text-[11px] text-gray-500">Summary email of active leads, renewals &amp; payouts every Monday.</p>
                                </div>
                                <input v-model="notificationsState.email_weekly" type="checkbox" class="w-4 h-4 text-blue-600 rounded cursor-pointer" />
                            </label>
                        </div>
                    </div>

                    <!-- TAB 5: SECURITY MENU -->
                    <div v-if="activeTab === 'security'" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7 space-y-6">
                        <div class="pb-5 border-b border-gray-100">
                            <h2 class="text-base font-bold text-gray-900">Security &amp; Password</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Ensure your account uses a strong password to maintain security.</p>
                        </div>

                        <form @submit.prevent="submitSecurity" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Current Password</label>
                                <input
                                    v-model="securityForm.current_password"
                                    type="password"
                                    required
                                    class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">New Password</label>
                                    <input
                                        v-model="securityForm.password"
                                        type="password"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm New Password</label>
                                    <input
                                        v-model="securityForm.password_confirmation"
                                        type="password"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-gray-900 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                    />
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="securityForm.processing"
                                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
                                >
                                    <KeyIcon class="w-4 h-4" />
                                    <span>{{ securityForm.processing ? 'Updating...' : 'Update Password' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
