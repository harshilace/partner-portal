<script setup>
import { ref, computed, watch } from 'vue';
import {
    MagnifyingGlassIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    FunnelIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    /** Array of row objects */
    rows: { type: Array, default: () => [] },
    /** Column definitions: { key, label, sortable?, filterable?, class? } */
    columns: { type: Array, required: true },
    /** Rows per page options */
    pageSizes: { type: Array, default: () => [10, 25, 50, 100] },
    /** Empty state message */
    emptyMessage: { type: String, default: 'No records found.' },
    /** Show global search box */
    searchable: { type: Boolean, default: true },
    /** Loading state */
    loading: { type: Boolean, default: false },
});

// ── Search ───────────────────────────────────────────────
const search = ref('');

// ── Column Filters ───────────────────────────────────────
const showFilters = ref(false);
const columnFilters = ref({});

const filterableColumns = computed(() =>
    props.columns.filter((c) => c.filterable)
);

const activeColumnCount = computed(() =>
    Object.values(columnFilters.value).filter((v) => v !== undefined && v !== null && String(v).trim() !== '').length
);

// ── Sorting ──────────────────────────────────────────────
const sortKey = ref('');
const sortDir = ref('asc');

const toggleSort = (col) => {
    if (!col.sortable) return;
    if (sortKey.value === col.key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = col.key;
        sortDir.value = 'asc';
    }
};

// ── Pagination ───────────────────────────────────────────
const pageSize = ref(props.pageSizes[0]);
const currentPage = ref(1);

// Reset page on search/filter change
watch([search, columnFilters, pageSize], () => { currentPage.value = 1; }, { deep: true });

// ── Computed Pipeline ────────────────────────────────────
const filtered = computed(() => {
    let data = [...props.rows];

    // Global search
    const q = search.value.trim().toLowerCase();
    if (q) {
        data = data.filter((row) =>
            props.columns.some((col) => {
                const val = String(row[col.key] ?? '').toLowerCase();
                return val.includes(q);
            })
        );
    }

    // Column filters
    for (const [key, val] of Object.entries(columnFilters.value)) {
        if (val === undefined || val === null || String(val).trim() === '') continue;
        const targetVal = String(val).trim().toLowerCase();
        const col = props.columns.find((c) => c.key === key);

        data = data.filter((row) => {
            const raw = row[key];
            if (raw === undefined || raw === null) return false;

            // If column has predefined options or is boolean, use exact match
            if (col && col.options && col.options.length > 0) {
                return String(raw).toLowerCase() === targetVal;
            }
            if (typeof raw === 'boolean') {
                return String(raw).toLowerCase() === targetVal;
            }

            const rowVal = String(raw).toLowerCase();
            return rowVal === targetVal || rowVal.includes(targetVal);
        });
    }

    return data;
});

const sorted = computed(() => {
    if (!sortKey.value) return filtered.value;
    return [...filtered.value].sort((a, b) => {
        const av = a[sortKey.value] ?? '';
        const bv = b[sortKey.value] ?? '';
        const cmp = String(av).localeCompare(String(bv), undefined, { numeric: true, sensitivity: 'base' });
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(sorted.value.length / pageSize.value)));

const paginated = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return sorted.value.slice(start, start + pageSize.value);
});

const pageInfo = computed(() => {
    const total = sorted.value.length;
    const start = total === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1;
    const end = Math.min(currentPage.value * pageSize.value, total);
    return { start, end, total };
});

const pageNumbers = computed(() => {
    const pages = [];
    const tp = totalPages.value;
    const cp = currentPage.value;
    if (tp <= 7) {
        for (let i = 1; i <= tp; i++) pages.push(i);
    } else {
        pages.push(1);
        if (cp > 3) pages.push('...');
        for (let i = Math.max(2, cp - 1); i <= Math.min(tp - 1, cp + 1); i++) pages.push(i);
        if (cp < tp - 2) pages.push('...');
        pages.push(tp);
    }
    return pages;
});

const activeFilterBadges = computed(() => {
    const list = [];
    if (search.value.trim()) {
        list.push({
            type: 'search',
            key: 'search',
            label: 'Search',
            displayValue: `Search: "${search.value.trim()}"`,
        });
    }
    for (const [key, val] of Object.entries(columnFilters.value)) {
        if (val !== undefined && val !== null && String(val).trim() !== '') {
            const col = props.columns.find((c) => c.key === key);
            const colLabel = col ? col.label : key;
            let displayVal = String(val).trim();
            if (col && col.options) {
                const opt = col.options.find((o) => (typeof o === 'object' ? o.value : o) === val);
                if (opt && typeof opt === 'object') {
                    displayVal = opt.label;
                }
            }
            list.push({
                type: 'column',
                key: key,
                label: colLabel,
                displayValue: `${colLabel}: ${displayVal}`,
            });
        }
    }
    return list;
});

const removeFilter = (badge) => {
    if (badge.type === 'search') {
        search.value = '';
    } else if (badge.type === 'column') {
        columnFilters.value[badge.key] = '';
    }
};

const clearFilters = () => {
    search.value = '';
    columnFilters.value = {};
    sortKey.value = '';
    sortDir.value = 'asc';
    currentPage.value = 1;
};

const hasActiveFilters = computed(() => activeFilterBadges.value.length > 0);
</script>

<template>
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <!-- ── Toolbar ─────────────────────────────────────── -->
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <!-- Search -->
            <div v-if="searchable" class="relative flex-1 max-w-xs">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 outline-none transition-all placeholder-gray-400"
                />
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <!-- Column filters toggle -->
                <button
                    v-if="filterableColumns.length > 0"
                    type="button"
                    @click="showFilters = !showFilters"
                    :class="[
                        'flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl transition-colors border relative',
                        showFilters || activeColumnCount > 0
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'text-gray-600 hover:bg-gray-100 border-gray-200'
                    ]"
                >
                    <FunnelIcon class="w-3.5 h-3.5" />
                    <span>Filters</span>
                    <span v-if="activeColumnCount > 0" class="ml-0.5 px-1.5 py-0.5 text-[10px] bg-white text-blue-600 rounded-full font-bold leading-none">
                        {{ activeColumnCount }}
                    </span>
                </button>

                <!-- Clear / Reset filters button in header -->
                <button
                    v-if="hasActiveFilters"
                    type="button"
                    @click="clearFilters"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-colors border border-red-200"
                >
                    <XMarkIcon class="w-3.5 h-3.5" />
                    Reset Filters
                </button>

                <!-- Page size -->
                <select
                    v-model="pageSize"
                    class="text-xs px-2 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500/15 outline-none cursor-pointer"
                >
                    <option v-for="s in pageSizes" :key="s" :value="s">{{ s }} / page</option>
                </select>
            </div>
        </div>

        <!-- ── Column Filters Row ─────────────────────────── -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="showFilters && filterableColumns.length > 0" class="px-5 py-3.5 bg-blue-50/50 border-b border-blue-100 flex flex-wrap gap-4 items-end">
                <div v-for="col in filterableColumns" :key="col.key" class="flex flex-col gap-1 min-w-40">
                    <label class="text-[10px] font-semibold text-blue-700 uppercase tracking-wider">{{ col.label }}</label>
                    <select
                        v-if="col.options && col.options.length > 0"
                        v-model="columnFilters[col.key]"
                        class="px-2.5 py-1.5 text-xs rounded-lg border border-blue-200 bg-white text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 outline-none cursor-pointer"
                    >
                        <option value="">All {{ col.label }}s</option>
                        <option
                            v-for="opt in col.options"
                            :key="typeof opt === 'object' ? opt.value : opt"
                            :value="typeof opt === 'object' ? opt.value : opt"
                        >
                            {{ typeof opt === 'object' ? opt.label : opt }}
                        </option>
                    </select>
                    <input
                        v-else
                        v-model="columnFilters[col.key]"
                        type="text"
                        :placeholder="`Filter ${col.label}...`"
                        class="px-2.5 py-1.5 text-xs rounded-lg border border-blue-200 bg-white text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 outline-none"
                    />
                </div>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    @click="clearFilters"
                    class="self-end px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-100/60 rounded-lg transition-colors border border-red-200 flex items-center gap-1 bg-white cursor-pointer"
                >
                    <XMarkIcon class="w-3.5 h-3.5" />
                    Reset All
                </button>
            </div>
        </Transition>

        <!-- ── Applied Active Filters Badges ─────────────────── -->
        <div v-if="activeFilterBadges.length > 0" class="px-5 py-2.5 bg-slate-50/80 border-b border-gray-100 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-gray-500 font-medium text-[11px] mr-1 flex items-center gap-1">
                <FunnelIcon class="w-3.5 h-3.5 text-blue-600" />
                Applied Filters:
            </span>
            <div
                v-for="badge in activeFilterBadges"
                :key="badge.key"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-blue-200 text-blue-700 rounded-full text-xs font-medium shadow-2xs transition-all hover:border-blue-300"
            >
                <span>{{ badge.displayValue }}</span>
                <button
                    type="button"
                    @click="removeFilter(badge)"
                    class="w-4 h-4 rounded-full hover:bg-blue-100 flex items-center justify-center text-blue-500 hover:text-blue-800 transition-colors ml-0.5 cursor-pointer"
                    :title="`Remove ${badge.label} filter`"
                >
                    <XMarkIcon class="w-3 h-3" />
                </button>
            </div>
            <button
                type="button"
                @click="clearFilters"
                class="text-xs text-red-600 hover:text-red-700 font-semibold hover:underline ml-auto flex items-center gap-1 px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors cursor-pointer"
            >
                <XMarkIcon class="w-3.5 h-3.5" />
                Reset All Filters
            </button>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="loading" class="py-16 flex flex-col items-center gap-3 text-gray-400">
            <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
            <span class="text-sm">Loading...</span>
        </div>

        <!-- ── Table ──────────────────────────────────────── -->
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            scope="col"
                            :class="['px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap', col.sortable ? 'cursor-pointer select-none hover:text-blue-600 hover:bg-blue-50/50 transition-colors' : '', col.class || '']"
                            @click="toggleSort(col)"
                        >
                            <div class="flex items-center gap-1.5">
                                <span>{{ col.label }}</span>
                                <span v-if="col.sortable" class="flex flex-col gap-px ml-0.5">
                                    <ChevronUpIcon :class="['w-2.5 h-2.5 transition-colors', sortKey === col.key && sortDir === 'asc' ? 'text-blue-600' : 'text-gray-300']" />
                                    <ChevronDownIcon :class="['w-2.5 h-2.5 transition-colors', sortKey === col.key && sortDir === 'desc' ? 'text-blue-600' : 'text-gray-300']" />
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    <tr v-if="paginated.length === 0">
                        <td :colspan="columns.length" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <MagnifyingGlassIcon class="w-10 h-10 text-gray-200 stroke-1" />
                                <p class="text-sm font-medium text-gray-500">{{ emptyMessage }}</p>
                                <button v-if="hasActiveFilters" @click="clearFilters" type="button" class="text-xs text-blue-600 hover:underline">Clear filters</button>
                            </div>
                        </td>
                    </tr>
                    <tr
                        v-for="(row, idx) in paginated"
                        :key="row.id ?? idx"
                        class="hover:bg-blue-50/30 transition-colors group"
                    >
                        <slot name="row" :row="row" :columns="columns" />
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Pagination ─────────────────────────────────── -->
        <div v-if="!loading && sorted.length > 0" class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-gray-500">
                Showing <span class="font-semibold text-gray-800">{{ pageInfo.start }}–{{ pageInfo.end }}</span> of <span class="font-semibold text-gray-800">{{ pageInfo.total }}</span> records
            </p>

            <div class="flex items-center gap-1">
                <button
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    class="p-1.5 rounded-lg text-gray-500 hover:bg-white hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors border border-transparent hover:border-gray-200"
                    aria-label="Previous page"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>

                <button
                    v-for="p in pageNumbers"
                    :key="p"
                    @click="typeof p === 'number' && (currentPage = p)"
                    :disabled="p === '...'"
                    :class="['min-w-8 h-8 px-2 text-xs rounded-lg transition-colors font-medium', p === currentPage ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : p === '...' ? 'cursor-default text-gray-400' : 'text-gray-600 hover:bg-white hover:text-blue-600 border border-transparent hover:border-gray-200']"
                >
                    {{ p }}
                </button>

                <button
                    @click="currentPage++"
                    :disabled="currentPage === totalPages"
                    class="p-1.5 rounded-lg text-gray-500 hover:bg-white hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors border border-transparent hover:border-gray-200"
                    aria-label="Next page"
                >
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
