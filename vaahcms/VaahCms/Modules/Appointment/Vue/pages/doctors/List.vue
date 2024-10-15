<script setup>
import { onMounted, reactive, ref, computed, watch } from "vue"; // Import watch
import { useRoute } from 'vue-router';

import { useDoctorStore } from '../../stores/store-doctors';
import { useRootStore } from '../../stores/root';

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue';
import DoctorFilter from './components/DoctorFilter.vue';

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

const store = useDoctorStore();
const root = useRootStore();
const route = useRoute();

onMounted(async () => {
    document.title = 'Doctors - Appointment';
    store.item = null;

    await store.onLoad(route);
    await store.watchRoutes(route);
    await store.watchStates();
    await store.getAssets();
    await store.getList();
    await store.getListCreateMenu();
});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu

const dynamicClass = computed(() => {
    if (store.quick_filters_doctors) {
        return 'col-9';
    } else {
        return 'col-' + (store.show_filters ? 9 : store.list_view_width);
    }
});

watch(() => store.show_filters, (newValue) => {
    if (newValue) {
        store.quick_filters_doctors = false;
    }
});

watch(() => store.quick_filters_doctors, (newValue) => {
    if (newValue) {
        store.show_filters = false;
    }
});

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
            importDoctors(json_data);
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

const exportDoctors = () => {
    store.exportDoctors();
};

const importDoctors = (json_data) => {
    store.importDoctors(json_data);
};
</script>

<template>
    <div class="grid" v-if="store.assets">
        <div :class="dynamicClass">
            <Panel class="is-small">

                <template class="p-1" #header>
                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Doctors</b>
                            <Badge v-if="store.list && store.list.total > 0" :value="store.list.total" />
                        </div>
                    </div>
                </template>

                <template #icons>
                    <div class="p-inputgroup">
                        <Button v-if="store.assets.permission.includes('appointment-has-access-of-patient')" data-testid="doctors-list-create"
                                class="p-button-sm"
                                @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>


                        <Button @click="openFileDialog" class="import-btn">Upload Doctors CSV</Button>


                        <Button label="Export Doctors"
                                @click="exportDoctors"
                                class="export-btn"
                                style="margin-left: 5px;"
                        />

                        <!-- Reload Button -->
                        <Button data-testid="doctors-list-reload"
                                class="p-button-sm"
                                @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>

                        <!-- form_menu -->
                        <Button v-if="root.assets && root.assets.module && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"
                                data-testid="doctors-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true"/>

                        <Menu ref="create_menu" :model="store.list_create_menu" :popup="true" />
                        <!-- /form_menu -->
                    </div>
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>

        <Filters />
        <DoctorFilter />
        <RouterView />

        <Dialog header="Upload Doctors CSV"
                :visible.sync="isDialogVisible"
                modal
                :closable="true"
                :dismissable-mask="true"
                :close-on-escape="true"
                class="custom-dialog"
        >
            <div class="p-fluid">
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" />
            </div>
            <template #footer>
                <Button label="Close" @click="isDialogVisible = false" class="p-button-text" />
            </template>
        </Dialog>
    </div>
</template>


<style scoped>

.custom-dialog {
    max-width: 600px;
    width: 100%;
    height: auto;
    background-color: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}


.custom-dialog .p-dialog-header {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
}


.custom-dialog .p-dialog-footer {
    display: flex;
    justify-content: flex-end;
    padding: 10px;
}

.custom-dialog input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #ced4da;
    border-radius: 5px;
}
.import-btn {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    margin-left: 5px;
}
.export-btn {
    background-color: mediumvioletred;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    margin-left: 5px;
    margin-right: 5px;
}
</style>
