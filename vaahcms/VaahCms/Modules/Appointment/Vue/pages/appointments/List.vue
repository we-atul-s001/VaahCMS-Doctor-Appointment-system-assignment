<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useAppointmentStore} from '../../stores/store-appointments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'

const store = useAppointmentStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Book Appointments - Appointment';
    store.item = null;
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

    /**
     * watch routes to update view, column width
     * and get new item when routes get changed
     */
    await store.watchRoutes(route);

    /**
     * watch states like `query.filter` to
     * call specific actions if a state gets
     * changed
     */
    await store.watchStates();

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();

    /**
     * fetch list of records
     */
    await store.getList();

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu
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

const exportAppointment = () => {
    store.exportAppointment();
};

const importAppointment = (json_data) => {
    store.importAppointment(json_data);
};

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+(store.show_filters?9:store.list_view_width)">
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Book Appointments</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                    <Button v-if="(store.assets.permission.includes('appointment-has-access-of-patient')
                     && !store.assets.permission.includes('appointment-has-access-of-doctor'))
                     || (store.assets.permission.includes('appointment-has-access-of-patient')
                      && store.assets.permission.includes('appointment-has-access-of-doctor'))"
                            data-testid="appointments-list-create"
                            class="p-button-sm"
                            @click="store.toForm()">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                        <Button @click="openFileDialog" class="import-btn">Upload CSV</Button>


                        <Button label="Export CSV"
                                @click="exportAppointment"
                                class="export-btn"
                                style="margin-left: 5px;"
                        />
                    <Button data-testid="appointments-list-reload"
                            class="p-button-sm"
                            @click="store.getList()">
                        <i class="pi pi-refresh mr-1"></i>
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="appointments-create-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="create_menu"
                          :model="store.list_create_menu"
                          :popup="true" />

                    <!--/form_menu-->

                    </div>

                </template>

                <Actions/>

                <Table/>

            </Panel>
        </div>

         <Filters/>

        <RouterView/>

        <Dialog header="Upload CSV"
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
