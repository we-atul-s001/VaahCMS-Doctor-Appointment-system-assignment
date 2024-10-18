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

const openImportDialog = () => {
    isImportDialogVisible.value = true;
    currentStep.value = 1;
    selectedFile.value = null;
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

const downloadSampleCSV = () => {
    console.log('Downloading sample CSV...');
};

const goBack = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const goNext = () => {
    if (currentStep.value < 4) {
        currentStep.value++;
    }
};

const closeImportDialog = () => {
    isImportDialogVisible.value = false;
};

const exportAppointment = () => {
    store.exportAppointment();
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
                            </div>
                        </div>
                        <div class="mt-2">
                            <Button label="Download Sample CSV" icon="pi pi-download" @click="downloadSampleCSV" class="p-button-secondary" />
                        </div>
                    </div>

                    <div v-else-if="currentStep === 2">
                        <h2>Map Fields</h2>

                    </div>

                    <div v-else-if="currentStep === 3">
                        <h2>Preview Data</h2>

                    </div>

                    <div v-else>
                        <h2>Import Result</h2>

                    </div>
                </div>

                <div class="mt-4 flex justify-content-between">
                    <Button label="Back" icon="pi pi-chevron-left" @click="goBack" :disabled="currentStep === 1" />
                    <Button label="Next" icon="pi pi-chevron-right" iconPos="right" @click="goNext" :disabled="currentStep === 4" />
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" @click="closeImportDialog" class="p-button-text" />
            </template>
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
</style>
