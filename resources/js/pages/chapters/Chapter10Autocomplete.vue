<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useAutocomplete } from "../../composables/useAutocomplete";
import { useIndexedTokens } from "../../composables/useIndexedTokens";
import type { Suggestion } from "../../types/autocomplete";

const {
    query,
    suggestions,
    loading,
    error,
    tookMs,
    sent,
    cancelled,
    setQuery,
} = useAutocomplete();
const { tokens, error: tokensError } = useIndexedTokens(
    "Smartphone",
    "autocomplete_index",
);

const open = ref<boolean>(false);
const activeIndex = ref<number>(-1);
const selected = ref<Suggestion | null>(null);

const activeId = computed(() =>
    activeIndex.value >= 0 ? `suggestion-${activeIndex.value}` : undefined,
);

watch(suggestions, () => {
    activeIndex.value = -1;
});

function onInput(): void {
    selected.value = null;
    open.value = true;
}

function move(step: number): void {
    if (suggestions.value.length === 0) {
        return;
    }

    open.value = true;
    activeIndex.value =
        (activeIndex.value + step + suggestions.value.length) %
        suggestions.value.length;
}

function select(suggestion: Suggestion): void {
    selected.value = suggestion;
    setQuery(suggestion.name, true);
    open.value = false;
    activeIndex.value = -1;
}

function confirm(): void {
    const suggestion = suggestions.value[activeIndex.value];

    if (suggestion) {
        select(suggestion);
    }
}
</script>

<template>
    <section class="max-w-3xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 10 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Autocomplete</h1>
        <p class="mt-3 text-gray-600">
            Autocomplete must answer on every keystroke, so the work is moved to
            index time: each word is stored as its prefixes (<em
                >edge n-grams</em
            >). A query like <code>smart</code> then matches an indexed token
            directly, without scanning the index.
        </p>

        <div class="relative mt-6">
            <label for="autocomplete-input" class="text-sm text-gray-600"
                >Start typing a product name (2+ letters)</label
            >
            <input
                id="autocomplete-input"
                v-model="query"
                type="text"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-controls="suggestion-list"
                :aria-expanded="open && suggestions.length > 0"
                :aria-activedescendant="activeId"
                placeholder="e.g. smart, wire hea, nova"
                class="mt-1 w-full rounded border border-gray-300 px-4 py-2"
                @input="onInput"
                @focus="open = true"
                @blur="open = false"
                @keydown.down.prevent="move(1)"
                @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="confirm"
                @keydown.esc="open = false"
            />

            <ul
                v-if="open && suggestions.length > 0"
                id="suggestion-list"
                role="listbox"
                class="absolute z-10 mt-1 w-full overflow-hidden rounded border border-gray-200 bg-white shadow-lg"
            >
                <li
                    v-for="(suggestion, index) in suggestions"
                    :id="`suggestion-${index}`"
                    :key="suggestion.id"
                    role="option"
                    :aria-selected="index === activeIndex"
                    class="cursor-pointer px-4 py-2 text-sm"
                    :class="
                        index === activeIndex
                            ? 'bg-indigo-50 text-indigo-900'
                            : 'hover:bg-gray-50'
                    "
                    @mousedown.prevent="select(suggestion)"
                    @mousemove="activeIndex = index"
                >
                    <span class="font-medium">{{ suggestion.name }}</span>
                    <span class="ml-2 text-xs text-gray-500"
                        >{{ suggestion.category }} &middot;
                        {{ suggestion.brand }}</span
                    >
                </li>
            </ul>
        </div>

        <p
            v-if="error"
            class="mt-3 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <dl class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Status</dt>
                <dd class="mt-1 text-sm font-medium">
                    {{ loading ? "Searching..." : "Idle" }}
                </dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Last response</dt>
                <dd class="mt-1 text-sm font-medium">
                    {{ tookMs === null ? "-" : `${tookMs} ms` }}
                </dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Requests sent</dt>
                <dd class="mt-1 text-sm font-medium">{{ sent }}</dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Cancelled</dt>
                <dd class="mt-1 text-sm font-medium">{{ cancelled }}</dd>
            </div>
        </dl>

        <p
            v-if="selected"
            class="mt-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800"
        >
            Selected product #{{ selected.id }}:
            <strong>{{ selected.name }}</strong>
        </p>

        <div class="mt-10 rounded border border-gray-200 bg-white p-5">
            <h2 class="font-semibold">
                How "Smartphone" is stored in the index
            </h2>
            <p v-if="tokensError" class="mt-2 text-sm text-yellow-800">
                {{ tokensError }}
            </p>
            <p v-else class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="(token, index) in tokens"
                    :key="index"
                    class="rounded bg-indigo-50 px-2 py-0.5 font-mono text-xs text-indigo-800"
                >
                    {{ token.token }}
                </span>
            </p>
            <p class="mt-3 text-sm text-gray-600">
                These are the tokens of the
                <code>autocomplete_index</code> analyzer. Typing any of them
                matches the product. The search side does
                <strong>not</strong> use n-grams, so <code>smart</code> is
                looked up as one token instead of being split again.
            </p>
        </div>

        <div
            class="mt-6 rounded border border-gray-200 bg-white p-5 text-sm text-gray-600"
        >
            <h2 class="font-semibold text-gray-900">Debounce and cancelling</h2>
            <p class="mt-2">
                The input waits 250 ms after the last keystroke before calling
                the API (debounce), and a newer request aborts the one still in
                flight. Type quickly and watch <em>Cancelled</em> grow while
                <em>Requests sent</em>
                stays far below the number of keystrokes.
            </p>
        </div>
    </section>
</template>
