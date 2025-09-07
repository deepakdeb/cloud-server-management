<template>
    <div class="p-6 bg-gray-100 min-h-screen">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Servers</h2>
            <button
                class="mt-4 md:mt-0 px-4 py-2 bg-indigo-600 text-white rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                @click="showFormModal()"
            >
                Add New Server
            </button>
        </div>

        <!-- Bulk Actions and Filter/Search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div class="flex items-center space-x-2">
                <select
                    v-model="bulkAction"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete Selected</option>
                    <option value="active">Set Status to Active</option>
                    <option value="inactive">Set Status to Inactive</option>
                    <option value="maintenance">Set Status to Maintenance</option>
                </select>
                <button
                    @click="performBulkAction"
                    :disabled="selectedServers.length === 0"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md shadow-md hover:bg-gray-700 disabled:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    Apply
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:w-2/3">
                <div class="col-span-1">
                    <input
                        type="text"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Search by name or IP..."
                        v-model="search"
                        @keyup.enter="fetchServers()"
                    />
                </div>
                <div class="col-span-1">
                    <select
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        v-model="filterProvider"
                        @change="fetchServers()"
                    >
                        <option value="">All Providers</option>
                        <option v-for="provider in providers" :value="provider" :key="provider">
                            {{ provider }}
                        </option>
                    </select>
                </div>
                <div class="col-span-1">
                    <select
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        v-model="filterStatus"
                        @change="fetchServers()"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Server List Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3">
                            <input
                                type="checkbox"
                                @change="toggleSelectAll"
                                :checked="selectAll"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            @click="sortBy('id')"
                        >
                            ID
                            <i v-if="sortColumn === 'id'" :class="sortClass"></i>
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            @click="sortBy('name')"
                        >
                            Name
                            <i v-if="sortColumn === 'name'" :class="sortClass"></i>
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            IP Address
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Provider
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            CPU Cores
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            RAM (MB)
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Storage (GB)
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="server in servers" :key="server.id">
                        <td class="px-2 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                :value="server.id"
                                v-model="selectedServers"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.ip_address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.provider }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="statusColor(server.status)"
                            >
                                {{ server.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.cpu_cores }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.ram_mb }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ server.storage_gb }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                @click="showServer(server)"
                            >
                                View
                            </button>
                            <button
                                class="text-yellow-600 hover:text-yellow-900 mr-4"
                                @click="showFormModal(server)"
                            >
                                Edit
                            </button>
                            <button
                                class="text-red-600 hover:text-red-900"
                                @click="deleteServer(server.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :disabled="!pagination?.prev_page_url"
                    @click.prevent="fetchServers(pagination?.prev_page_url)"
                >
                    <span class="sr-only">Previous</span>
                    <!-- Heroicon name: solid/chevron-left -->
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
                <a
                    v-for="page in totalPages"
                    :key="page"
                    :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        pagination?.current_page === page
                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    ]"
                    href="#"
                    @click.prevent="fetchServers(`${pagination?.path}?page=${page}`)"
                >
                    {{ page }}
                </a>
                <button
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :disabled="!pagination?.next_page_url"
                    @click.prevent="fetchServers(pagination?.next_page_url)"
                >
                    <span class="sr-only">Next</span>
                    <!-- Heroicon name: solid/chevron-right -->
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </nav>
        </div>

        <!-- Success/Error Notification -->
        <div v-if="notification.message"
             :class="['fixed bottom-5 right-5 p-4 rounded-lg shadow-lg text-white', notification.type === 'success' ? 'bg-green-500' : 'bg-red-500']">
            {{ notification.message }}
        </div>

        <!-- Modal for Form -->
        <div
            class="fixed z-10 inset-0 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
            v-if="showModal"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                >
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ formMode === 'create' ? 'Add New Server' : 'Edit Server' }}
                            </h3>
                            <div class="mt-2">
                                <ServerForm
                                    :server="selectedServer"
                                    :form-mode="formMode"
                                    @close="formModalClose"
                                    @server-saved="handleServerSaved"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import ServerForm from './ServerForm.vue';

export default {
    components: {
        ServerForm,
    },
    data() {
        return {
            servers: [],
            pagination: {},
            selectedServer: null,
            formMode: 'create',
            search: '',
            filterProvider: '',
            filterStatus: '',
            sortColumn: 'id',
            sortDirection: 'desc',
            providers: ['aws', 'digitalocean', 'vultr', 'other'],
            showModal: false,
            selectedServers: [],
            bulkAction: '',
            notification: {
                message: '',
                type: '',
            },
        };
    },
    computed: {
        totalPages() {
            return this.pagination?.last_page || 1;
        },
        sortClass() {
            return this.sortDirection === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
        },
        selectAll: {
            get() {
                return this.servers.length > 0 && this.selectedServers.length === this.servers.length;
            },
            set(value) {
                this.selectedServers = value ? this.servers.map(server => server.id) : [];
            }
        },
    },
    mounted() {
        this.fetchServers();
    },
    methods: {
        fetchServers(pageUrl = 'servers') {
            const params = {
                search: this.search,
                provider: this.filterProvider,
                status: this.filterStatus,
                sort_by: this.sortColumn,
                sort_order: this.sortDirection,
            };

            axios
                .get(pageUrl, { params })
                .then((response) => {
                    this.servers = response.data.data;
                    this.pagination = response.data;
                    this.selectedServers = [];
                })
                .catch((error) => {
                    this.showNotification('Error fetching servers.', 'error');
                    console.error('Error fetching servers:', error);
                });
        },
        deleteServer(id) {
            if (confirm('Are you sure you want to delete this server?')) {
                const originalServers = [...this.servers];
                this.servers = this.servers.filter(server => server.id !== id);

                axios
                    .delete(`servers/${id}`)
                    .then(() => {
                        this.showNotification('Server deleted successfully.', 'success');
                    })
                    .catch((error) => {
                        this.servers = originalServers;
                        this.showNotification('Failed to delete server.', 'error');
                        console.error('Error deleting server:', error);
                    });
            }
        },
        formModalClose() {
            this.showModal = false;
        },
        showFormModal(server = null) {
            this.selectedServer = server;
            this.formMode = server ? 'edit' : 'create';
            this.showModal = true;
        },
        handleServerSaved() {
            this.showModal = false;
            this.fetchServers();
        },
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.fetchServers();
        },
        statusColor(status) {
            switch (status) {
                case 'active':
                    return 'bg-green-100 text-green-800';
                case 'inactive':
                    return 'bg-gray-200 text-gray-800';
                case 'maintenance':
                    return 'bg-yellow-100 text-yellow-800';
                default:
                    return 'bg-blue-100 text-blue-800';
            }
        },
        showServer(server) {
            // Using a simple alert for now.
            alert(`Viewing server: ${server.name}\nIP: ${server.ip_address}\nProvider: ${server.provider}`);
        },
        toggleSelectAll() {
            if (this.selectedServers.length === this.servers.length) {
                this.selectedServers = [];
            } else {
                this.selectedServers = this.servers.map(server => server.id);
            }
        },
        performBulkAction() {
            if (!this.bulkAction || this.selectedServers.length === 0) {
                this.showNotification('Please select an action and at least one server.', 'error');
                return;
            }
            if (!confirm(`Are you sure you want to perform "${this.bulkAction}" on ${this.selectedServers.length} servers?`)) {
                return;
            }

            const originalServers = [...this.servers];
            const originalSelection = [...this.selectedServers];

            let payload = {
                action: this.bulkAction,
                ids: this.selectedServers
            };
            if(this.bulkAction != 'delete'){
                payload.status = this.bulkAction;
                payload.action = 'update_status';
            }

            // Perform the API call
            axios.post('servers/bulk-actions', payload)
                .then(response => {
                    this.showNotification(response.data.message, 'success');
                    
                    // Optimistic UI for bulk delete
                    if (this.bulkAction === 'delete') {
                        this.servers = this.servers.filter(server => !this.selectedServers.includes(server.id));
                        this.selectedServers = [];
                    }else{
                        this.servers = this.servers.map(server => {
                            if (this.selectedServers.includes(server.id)) {
                                return { ...server, status: payload.status };
                            }
                            return server;
                        });
                        this.selectedServers = [];
                    }

                    this.bulkAction = '';
                })
                .catch(error => {
                    this.servers = originalServers;
                    this.selectedServers = originalSelection;
                    this.showNotification(error.response.data.error || 'Bulk action failed.', 'error');
                    console.error('Bulk action error:', error);
                });
        },
        showNotification(message, type) {
            this.notification.message = message;
            this.notification.type = type;
            setTimeout(() => {
                this.notification.message = '';
            }, 3000); // Notification disappears after 3 seconds
        },
    },
};
</script>
