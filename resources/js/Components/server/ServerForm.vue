<template>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <form @submit.prevent="saveServer">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Server Name -->
                <div class="col-span-1">
                    <label for="name" class="block text-sm font-medium text-gray-700">Server Name</label>
                    <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <div v-if="errors.name" class="mt-1 text-sm text-red-600">
                        {{ errors.name[0] }}
                    </div>
                </div>

                <!-- IP Address -->
                <div class="col-span-1">
                    <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                    <input
                        type="text"
                        id="ip_address"
                        v-model="form.ip_address"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <div v-if="errors.ip_address" class="mt-1 text-sm text-red-600">
                        {{ errors.ip_address[0] }}
                    </div>
                </div>

                <!-- Provider -->
                <div class="col-span-1">
                    <label for="provider" class="block text-sm font-medium text-gray-700">Provider</label>
                    <select
                        id="provider"
                        v-model="form.provider"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option disabled value="">Please select a provider</option>
                        <option>aws</option>
                        <option>digitalocean</option>
                        <option>vultr</option>
                        <option>other</option>
                    </select>
                    <div v-if="errors.provider" class="mt-1 text-sm text-red-600">
                        {{ errors.provider[0] }}
                    </div>
                </div>

                <!-- Status (only for edit mode) -->
                <div class="col-span-1" v-if="formMode === 'edit'">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option>active</option>
                        <option>inactive</option>
                        <option>maintenance</option>
                    </select>
                    <div v-if="errors.status" class="mt-1 text-sm text-red-600">
                        {{ errors.status[0] }}
                    </div>
                </div>

                <!-- CPU Cores -->
                <div class="col-span-1">
                    <label for="cpu_cores" class="block text-sm font-medium text-gray-700">CPU Cores</label>
                    <input
                        type="number"
                        id="cpu_cores"
                        v-model="form.cpu_cores"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <div v-if="errors.cpu_cores" class="mt-1 text-sm text-red-600">
                        {{ errors.cpu_cores[0] }}
                    </div>
                </div>

                <!-- RAM (MB) -->
                <div class="col-span-1">
                    <label for="ram_mb" class="block text-sm font-medium text-gray-700">RAM (MB)</label>
                    <input
                        type="number"
                        id="ram_mb"
                        v-model="form.ram_mb"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <div v-if="errors.ram_mb" class="mt-1 text-sm text-red-600">
                        {{ errors.ram_mb[0] }}
                    </div>
                </div>

                <!-- Storage (GB) -->
                <div class="col-span-1">
                    <label for="storage_gb" class="block text-sm font-medium text-gray-700">Storage (GB)</label>
                    <input
                        type="number"
                        id="storage_gb"
                        v-model="form.storage_gb"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <div v-if="errors.storage_gb" class="mt-1 text-sm text-red-600">
                        {{ errors.storage_gb[0] }}
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" class="inline-flex justify-center rounded-md border border-transparent bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-500" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500">
                    Save Server
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        server: {
            type: Object,
            default: null,
        },
        formMode: {
            type: String,
            default: 'create',
        },
    },
    data() {
        return {
            form: {
                name: '',
                ip_address: '',
                provider: '',
                status: 'active',
                cpu_cores: 1,
                ram_mb: 512,
                storage_gb: 10,
            },
            errors: {},
        };
    },
    watch: {
        server: {
            handler(newServer) {
                if (newServer) {
                    this.form = { ...newServer };
                } else {
                    this.resetForm();
                }
            },
            immediate: true,
        },
    },
    methods: {
        saveServer() {
            this.errors = {};
            if (this.formMode === 'create') {
                this.createServer();
            } else {
                this.updateServer();
            }
        },
        createServer() {
            axios
                .post('servers', this.form)
                .then((response) => {
                    this.$emit('server-saved');
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.error('Error creating server:', error);
                    }
                });
        },
        updateServer() {
            axios
                .put(`servers/${this.form.id}`, this.form)
                .then((response) => {
                    this.$emit('server-saved');
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.error('Error updating server:', error);
                    }
                });
        },
        resetForm() {
            this.form = {
                name: '',
                ip_address: '',
                provider: '',
                status: 'active',
                cpu_cores: 1,
                ram_mb: 512,
                storage_gb: 10,
            };
        },
    },
};
</script>
