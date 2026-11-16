<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import { useIndexedDocument } from "../../composables/useIndexedDocument";
import { useProducts } from "../../composables/useProducts";
import type { Product, ProductInput } from "../../types/product";

interface ProductForm extends Omit<ProductInput, "tags"> {
    tags: string;
}

const emptyForm = (): ProductForm => ({
    name: "",
    description: "",
    category: "",
    brand: "",
    price: 0,
    rating: 3,
    tags: "",
});

const {
    products,
    page,
    lastPage,
    summary,
    loading,
    errors,
    message,
    load,
    save,
    remove,
} = useProducts();
const indexed = useIndexedDocument();

const form = reactive<ProductForm>(emptyForm());
const editingId = ref<number | null>(null);

const inSync = computed(
    () =>
        summary.value !== null &&
        summary.value.database === summary.value.elasticsearch,
);

function resetForm(): void {
    Object.assign(form, emptyForm());
    editingId.value = null;
}

function edit(product: Product): void {
    Object.assign(form, { ...product, tags: product.tags.join(", ") });
    editingId.value = product.id;
}

async function submit(): Promise<void> {
    const tags = form.tags
        .split(",")
        .map((tag) => tag.trim())
        .filter((tag) => tag !== "");

    if (await save({ ...form, tags }, editingId.value)) {
        resetForm();
    }
}

async function destroy(product: Product): Promise<void> {
    await remove(product.id);

    if (indexed.productId.value === product.id) {
        indexed.productId.value = null;
    }
}

const fields: {
    key: "name" | "description" | "category" | "brand" | "tags";
    label: string;
}[] = [
    { key: "name", label: "Name" },
    { key: "description", label: "Description" },
    { key: "category", label: "Category" },
    { key: "brand", label: "Brand" },
    { key: "tags", label: "Tags (comma separated)" },
];
</script>

<template>
    <section class="max-w-5xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 2 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Indexing &amp; Auto-sync</h1>
        <p class="mt-3 text-gray-600">
            The <code>Searchable</code> trait keeps Elasticsearch in sync with
            the database. Create, edit or delete a product below and watch both
            counters move together, without any extra indexing code.
        </p>

        <div v-if="summary" class="mt-6 grid grid-cols-3 gap-4">
            <div class="rounded border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-400">Database rows</p>
                <p class="mt-1 text-2xl font-semibold">
                    {{ summary.database }}
                </p>
            </div>
            <div class="rounded border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-400">
                    Elasticsearch documents
                </p>
                <p class="mt-1 text-2xl font-semibold">
                    {{ summary.elasticsearch }}
                </p>
            </div>
            <div
                class="rounded border p-4"
                :class="
                    inSync
                        ? 'border-green-200 bg-green-50 text-green-800'
                        : 'border-yellow-200 bg-yellow-50 text-yellow-800'
                "
            >
                <p class="text-xs uppercase">Sync</p>
                <p class="mt-1 text-sm font-medium">
                    {{
                        inSync
                            ? "In sync"
                            : "Out of sync: run php artisan scout:import"
                    }}
                </p>
            </div>
        </div>

        <p
            v-if="message"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ message }}
        </p>

        <form
            class="mt-8 rounded border border-gray-200 bg-white p-5"
            @submit.prevent="submit"
        >
            <h2 class="font-semibold">
                {{
                    editingId === null
                        ? "New product"
                        : `Edit product #${editingId}`
                }}
            </h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label
                    v-for="field in fields"
                    :key="field.key"
                    class="block text-sm"
                >
                    <span class="text-gray-600">{{ field.label }}</span>
                    <input
                        v-model="form[field.key]"
                        type="text"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    />
                    <span
                        v-if="errors[field.key]"
                        class="text-xs text-red-600"
                        >{{ errors[field.key][0] }}</span
                    >
                </label>
                <label class="block text-sm">
                    <span class="text-gray-600">Price</span>
                    <input
                        v-model.number="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    />
                    <span v-if="errors.price" class="text-xs text-red-600">{{
                        errors.price[0]
                    }}</span>
                </label>
                <label class="block text-sm">
                    <span class="text-gray-600">Rating (1-5)</span>
                    <input
                        v-model.number="form.rating"
                        type="number"
                        step="0.1"
                        min="1"
                        max="5"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    />
                    <span v-if="errors.rating" class="text-xs text-red-600">{{
                        errors.rating[0]
                    }}</span>
                </label>
            </div>

            <div class="mt-4 flex gap-3">
                <button
                    type="submit"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    {{ editingId === null ? "Create" : "Save changes" }}
                </button>
                <button
                    v-if="editingId !== null"
                    type="button"
                    class="rounded border border-gray-300 px-4 py-2 text-sm"
                    @click="resetForm"
                >
                    Cancel
                </button>
            </div>
        </form>

        <table
            class="mt-8 w-full overflow-hidden rounded border border-gray-200 bg-white text-left text-sm"
        >
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="product in products"
                    :key="product.id"
                    class="border-t border-gray-100"
                >
                    <td class="px-4 py-2">{{ product.id }}</td>
                    <td class="px-4 py-2 font-medium">{{ product.name }}</td>
                    <td class="px-4 py-2">{{ product.category }}</td>
                    <td class="px-4 py-2">{{ product.price.toFixed(2) }}</td>
                    <td class="space-x-3 px-4 py-2 text-right">
                        <button
                            type="button"
                            class="text-indigo-600 hover:underline"
                            @click="indexed.show(product.id)"
                        >
                            Indexed doc
                        </button>
                        <button
                            type="button"
                            class="text-gray-600 hover:underline"
                            @click="edit(product)"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="text-red-600 hover:underline"
                            @click="destroy(product)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
                <tr v-if="!loading && products.length === 0">
                    <td colspan="5" class="px-4 py-4 text-gray-500">
                        No products yet.
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-between text-sm">
            <button
                type="button"
                class="rounded border border-gray-300 px-3 py-1 disabled:opacity-50"
                :disabled="page <= 1"
                @click="load(page - 1)"
            >
                Previous
            </button>
            <span class="text-gray-500">Page {{ page }} of {{ lastPage }}</span>
            <button
                type="button"
                class="rounded border border-gray-300 px-3 py-1 disabled:opacity-50"
                :disabled="page >= lastPage"
                @click="load(page + 1)"
            >
                Next
            </button>
        </div>

        <div v-if="indexed.productId.value !== null" class="mt-8">
            <h2 class="text-lg font-semibold">
                Document stored in Elasticsearch for product #{{
                    indexed.productId.value
                }}
            </h2>
            <p v-if="indexed.error.value" class="mt-2 text-sm text-red-600">
                {{ indexed.error.value }}
            </p>
            <pre
                v-else-if="indexed.document.value"
                class="mt-2 overflow-x-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                >{{ JSON.stringify(indexed.document.value, null, 2) }}</pre
            >
        </div>
    </section>
</template>
