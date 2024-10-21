<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from 'vue-router';
import { useConfirm } from "primevue/useconfirm";

import { useAppointmentStore } from '../../stores/store-appointments';
import { useRootStore } from '../../stores/root';

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue';

const store = useAppointmentStore();
const root = useRootStore();
const route = useRoute();
const confirm = useConfirm();

const isImportDialogVisible = ref(false);
const currentStep = ref(1);
const steps = ref([
    { label: 'Upload', value: 1 },
    { label: 'Map', value: 2 },
    { label: 'Preview', value: 3 },
    { label: 'Result', value: 4 }
]);
const selectedFile = ref(null);
const uploadedFileName = ref("");
const headers = ref([]);
const selectedHeaders = ref([]);
const previewData = ref([]);

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

const openImportDialog = async () => {
    isImportDialogVisible.value = true;
    currentStep.value = 1;
    selectedFile.value = null;
    uploadedFileName.value = "";
    headers.value = [];
    selectedHeaders.value = [];
    previewData.value = [];  // Clear preview data
};

const fileInput = ref(null);
const json_data_pass = ref(null);

const handleFileUpload = (event) => {
    const file = event.files[0];
    if (file) {
        uploadedFileName.value = file.name;

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const contents = e.target.result;
                json_data_pass.value = csvToJson(contents);
                headers.value = extractHeaders(contents);
                selectedHeaders.value = Array(store.assets.fields.length).fill(null);
                previewData.value = generatePreviewData(json_data_pass.value, selectedHeaders.value);
                goNext();  // Automatically go to the mapping step
            } catch (error) {
                console.error('Error processing the file:', error);
            }
        };

        reader.onerror = (error) => {
            console.error('Error reading file:', error);
        };

        reader.readAsText(file);
    } else {
        console.error('No file selected or file is invalid.');
    }
};

const triggerImportAppointment = () => {
    if (!json_data_pass.value) {
        console.error('No data available to import. Please upload a file.');
        return;
    }

    const importData = {
        csvData: json_data_pass.value,
        headerMapping: selectedHeaders.value
    };

    store.importAppointment(importData);
};

const csvToJson = (csv) => {
    const lines = csv.split("\n");
    const headers = lines[0].split(",");

    const jsonData = lines.slice(1).map(line => {
        const values = line.split(",");
        return headers.reduce((acc, header, index) => {
            acc[header.trim()] = values[index] ? values[index].trim() : null;
            return acc;
        }, {});
    });

    return jsonData;
};

const downloadSampleCSV = () => {
    console.log('Downloading sample CSV...');
};

const extractHeaders = (csv) => {
    const lines = csv.split("\n");
    const headers = lines[0].split(",").map(header => header.trim());
    return headers;
};

const goBack = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goNext = () => {
    if (currentStep.value === 1 && !uploadedFileName.value) {
        console.error('No file uploaded. Please upload a file to proceed.');
        return;
    }

    if (currentStep.value < steps.value.length) {
        currentStep.value++;
    } else if (currentStep.value === 2 && headers.length > 0) {
        currentStep.value++;
    } else if (currentStep.value === 3) {
        currentStep.value++;
    }
};

const closeImportDialog = () => {
    isImportDialogVisible.value = false;
};

const exportAppointment = () => {
    store.exportAppointment();
};

const setSelectedHeader = (dbHeader, selectedValue) => {
    selectedHeaders.value[dbHeader] = selectedValue;
    previewData.value = generatePreviewData(json_data_pass.value, selectedHeaders.value); // Update preview on header change
};

const generatePreviewData = (data, selectedHeaders) => {
    return data.map(item => {
        const previewItem = {};
        for (const dbHeader in selectedHeaders) {
            const csvHeader = selectedHeaders[dbHeader];
            previewItem[dbHeader] = csvHeader ? item[csvHeader] : null;  // Map CSV value to DB header
        }
        return previewItem;
    });
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

                        <Button @click="openImportDialog" class="p-button-sm">
                            <i class="pi pi-upload mr-1"></i>
                            Import
                        </Button>

                        <Button label="Export CSV" @click="exportAppointment" class="p-button-sm" style="margin-left: 5px;" />

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

        <Dialog header="Bulk Import" v-model:visible="isImportDialogVisible" :style="{width: '50vw'}" :modal="true">
            <div class="card">
                <Steps :model="steps" :readonly="false" :activeIndex="currentStep - 1" />

                <div class="mt-4">
                    <div v-if="currentStep === 1" class="upload-step">
                        <h2>Select a CSV file to Import</h2>
                        <div class="p-fluid">
                            <div class="p-field">
                                <FileUpload
                                    mode="basic"
                                    :auto="true"
                                    accept=".csv"
                                    :maxFileSize="1000000"
                                    @select="handleFileUpload"
                                    chooseLabel="Select File"
                                />
                                <p v-if="uploadedFileName" class="uploaded-file-name">
                                    Uploaded File: {{ uploadedFileName }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <Button label="Download Sample CSV" icon="pi pi-download" @click="downloadSampleCSV" class="p-button-secondary" />
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2">
                        <h2>Map Fields</h2>
                        <div class="header-mapping">
                            <div class="columns">
                                <div class="column">
                                    <h3>Database Headers</h3>
                                    <ul>
                                        <li v-for="(field, index) in store.assets.fields" :key="index">
                                            {{ field }}
                                        </li>
                                    </ul>
                                </div>

                                <div class="column">
                                    <h3>Extracted Headers</h3>
                                    <div v-if="headers.length > 0">
                                        <div v-for="(dbHeader, index) in store.assets.fields" :key="index">
                                            <Dropdown
                                                v-model="selectedHeaders[dbHeader]"
                                                :options="headers.map(h => h)"
                                                @change="(e) => setSelectedHeader(dbHeader, e.value)"
                                                placeholder="Select Header"
                                            />
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p>No headers extracted. Please upload a valid CSV file.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mapping-summary">
                            <h4>Current Mappings:</h4>
                            <ul>
                                <li v-for="(dbHeader, index) in store.assets.fields" :key="index">
                                    {{ dbHeader }}:
                                    <strong>{{ selectedHeaders[dbHeader] ? selectedHeaders[dbHeader] : 'Not Mapped' }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 3">
                        <h2>Preview Mapped Data</h2>
                        <div v-if="previewData.length > 0">
                            <table class="p-datatable">
                                <thead>
                                <tr>
                                    <th v-for="(dbHeader, index) in store.assets.fields" :key="index">
                                        {{ dbHeader }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in previewData" :key="index">
                                    <td v-for="(dbHeader, idx) in store.assets.fields" :key="idx">
                                        {{ item[dbHeader] }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else>
                            <p>No data to preview. Please ensure your CSV file has valid mappings.</p>
                        </div>
                    </div>

                    <div v-else-if="currentStep === 4">
                        <h2>Review and Confirm</h2>
                        <p>Please review your mappings before proceeding with the import.</p>
                    </div>

                    <div class="mt-4 flex justify-content-between">
                        <Button label="Back" icon="pi pi-chevron-left" @click="goBack" class="p-button-secondary"  :disabled="currentStep === 1" />
                        <Button label="Next" icon="pi pi-chevron-right" @click="goNext" class="p-button-primary" :disabled="currentStep === 4" />
                        <Button label="Finish" icon="pi pi-check" @click="triggerImportAppointment" v-if="currentStep === 4" />
                    </div>
                </div>
            </div>
        </Dialog>
    </div>
</template>

<style scoped>
.card {
    background: #ffffff;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.upload-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
    border: 2px dashed #ced4da;
    border-radius: 6px;
}

h2 {
    font-size: 1.2rem;
    margin-bottom: 1rem;
}
.columns {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    margin: 20px 0;
}

.column {
    width: 48%;
}

.header-mapping {
    margin-bottom: 1rem;
}

.p-datatable {
    width: 100%;
    border-collapse: collapse;
}

.p-datatable th,
.p-datatable td {
    border: 1px solid #ccc;
    padding: 0.5rem;
    text-align: left;
}

.mapping-summary {
    margin: 20px 0;
}
</style>
