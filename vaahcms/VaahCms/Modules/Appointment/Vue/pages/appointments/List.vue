<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from 'vue-router';

import { useAppointmentStore } from '../../stores/store-appointments';
import { useRootStore } from '../../stores/root';

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue';

const store = useAppointmentStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

onMounted(async () => {
    document.title = 'Book Appointments - Appointment';
    store.item = null;

    await store.onLoad(route);
    await store.watchRoutes(route);
    await store.watchStates();
    await store.getAssets();
    await store.getList();
    await store.getListCreateMenu();
});

const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};

const isDialogVisible = ref(false);

const openFileDialog = () => {
    isDialogVisible.value = true;
};

const fileInput = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const contents = e.target.result;
            const json_data = csvToJson(contents);
            console.log('Parsed JSON data:', json_data);
            importAppointment(json_data);
            isDialogVisible.value = false;
        };
        reader.readAsText(file);
    }
};

const csvToJson = (csv) => {
    const lines = csv.split('\n');
    const result = [];
    const headers = lines[0].split(',');

    for (let i = 1; i < lines.length; i++) {
        const obj = {};
        const currentLine = lines[i].split(',');
        for (let j = 0; j < headers.length; j++) {
            obj[headers[j].trim()] = currentLine[j] ? currentLine[j].trim() : '';
        }
        result.push(obj);
    }
    return result;
};

const exportAppointment = () => {
    store.exportAppointment();
};

const importAppointment = (json_data) => {
    store.importAppointment(json_data);
};
</script>

<template>
    <div class="grid" v-if="store.assets">
        <div :class="'col-'+(store.show_filters ? 9 : store.list_view_width)">
            <Panel class="is-small">
                <template class="p-1" #header>
                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Book Appointments</b>
                            <Badge v-if="store.list && store.list.total > 0" :value="store.list.total" />
                        </div>
                    </div>
                </template>

                <template #icons>
                    <div class="p-inputgroup">
                        <Button v-if="(store.assets.permission.includes('appointment-has-access-of-patient') && !store.assets.permission.includes('appointment-has-access-of-doctor')) || (store.assets.permission.includes('appointment-has-access-of-patient') && store.assets.permission.includes('appointment-has-access-of-doctor'))"
                                data-testid="appointments-list-create"
                                class="p-button-sm"
                                @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button @click="openFileDialog" class="import-btn">Upload CSV</Button>

                        <Button label="Export CSV" @click="exportAppointment" class="export-btn" style="margin-left: 5px;" />

                        <Button data-testid="appointments-list-reload" class="p-button-sm" @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>

                        <Button v-if="root.assets && root.assets.module && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"
                                data-testid="appointments-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true" />

                        <Menu ref="create_menu" :model="store.list_create_menu" :popup="true" />
                    </div>
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>

        <Filters />
        <RouterView />

        <Dialog header="Upload CSV"
                :visible.sync="isDialogVisible"
                modal
                :closable="true"
                :dismissable-mask="true"
                :close-on-escape="true"
                class="custom-dialog">
            <div class="p-fluid" style="background-color: #f5f5f5; padding: 20px; border-radius: 5px;">
                <div class="flex align-items-center">
                    <i class="pi pi-upload" style="font-size: 2em; color: #3f51b5;"></i>
                    <span style="font-size: 1.2em; color: #333;">Please select a CSV file to upload:</span>
                </div>
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="custom-file-input" />
            </div>
            <template #footer>
                <Button label="Close" @click="isDialogVisible = false" class="p-button-text" />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.custom-dialog .p-dialog {
    background-color: #f5f5f5;
    border-radius: 8px;
}

.custom-dialog .p-dialog-header {
    background-color: #e0e0e0;
    border-radius: 8px 8px 0 0;
    font-weight: bold;
    color: #333;
}

.custom-file-input {
    display: inline-block;
    padding: 10px 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.custom-file-input:hover {
    background-color: #e0e0e0;
    border-color: #3f51b5;
}

.custom-file-input:focus {
    outline: none;
    border-color: #3f51b5;
}
</style>
