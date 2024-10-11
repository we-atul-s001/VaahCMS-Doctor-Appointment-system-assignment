<script  setup>
import {ref, reactive, watch, onMounted} from 'vue';
import {useRoute} from 'vue-router';

import { useDoctorStore } from '../../../stores/store-doctors'

const store = useDoctorStore();
const route = useRoute();

onMounted(async () => {
    store.getListSelectedMenu();
    store.getListBulkMenu();
});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};
//--------/selected_menu_state

//--------bulk_menu_state
const bulk_menu_state = ref();
const toggleBulkMenuState = (event) => {
    bulk_menu_state.value.toggle(event);
};
//--------/bulk_menu_state
</script>

<template>
    <div>

        <!--actions-->
        <div :class="{'flex justify-content-between': store.isViewLarge()}" class="mt-2 mb-2">

            <!--left-->
            <div v-if="store.view === 'large'">

                <!--selected_menu-->
                <Button class="p-button-sm"
                    type="button"
                    @click="toggleSelectedMenuState"
                    data-testid="doctors-actions-menu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu">
                    <i class="pi pi-angle-down"></i>
                    <Badge v-if="store.action.items.length > 0"
                           :value="store.action.items.length" />
                </Button>
                <Menu ref="selected_menu_state"
                      :model="store.list_selected_menu"
                      :popup="true" />
                <!--/selected_menu-->

            </div>
            <!--/left-->

            <!--right-->
            <div >


                <div class="grid p-fluid">


                    <div class="col-12">
                        <div class="p-inputgroup ">

                            <InputText v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       class="p-inputtext-sm"
                                       @keyup.enter.native="store.delayedSearch()"
                                       @keyup.13="store.delayedSearch()"
                                       data-testid="doctors-actions-search"
                                       placeholder="Search"/>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    data-testid="doctors-actions-search-button"
                                    icon="pi pi-search"/>

                            <Button
                                type="button"
                                class="p-button-sm"
                                :disabled="Object.keys(route.params).length"
                                data-testid="doctors-actions-bulk-import"
                                @click="store.handleBulkImport()">
                                Bulk Import
                            </Button>
                            <Dialog
                                v-model:visible="store.show_import_dialog"
                                header="Bulk Import"
                                :visible="store.show_import_dialog"
                                :modal="true"
                                :closable="true"
                                :width="400"
                                class="custom-dialog"
                                @hide="store.onHideDialog()"
                            >
                                <div class="dialog-content">
                                    <h4>Select a File for Import</h4>
                                    <input
                                        type="file"
                                        accept=".xlsx"
                                        class="file-input"
                                        @change="store.onFileSelect($event)"
                                    />

                                </div>
                                <div class="p-dialog-footer custom-footer">
                                    <Button label="Cancel" @click="store.show_import_dialog = false" class="cancel-btn" />
                                    <Button label="Import" class="p-button-primary import-btn" @click="store.confirmBulkImport()" />
                                </div>
                            </Dialog>
                            <Button
                                type="button"
                                class="p-button-sm"
                                :disabled="Object.keys(route.params).length"
                                data-testid="doctors-actions-show-filters"
                                @click="store.show_filters = !store.show_filters">
                                Filters
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>



                            <Button
                                type="button"
                                icon="pi pi-filter-slash"
                                data-testid="doctors-actions-reset-filters"
                                class="p-button-sm"
                                label="Reset"
                                @click="store.resetQuery()" />

                                <!--bulk_menu-->
                                <Button
                                    type="button"
                                    @click="toggleBulkMenuState"
                                    severity="danger" outlined
                                    data-testid="doctors-actions-bulk-menu"
                                    aria-haspopup="true"
                                    aria-controls="bulk_menu_state"
                                    class="ml-1 p-button-sm">
                                    <i class="pi pi-ellipsis-v"></i>
                                </Button>
                                <Menu ref="bulk_menu_state"
                                      :model="store.list_bulk_menu"
                                      :popup="true" />
                                <!--/bulk_menu-->

                        </div>
                    </div>

                </div>

            </div>
            <!--/right-->

        </div>
        <!--/actions-->

    </div>
</template>
<style scoped>

.custom-dialog {
    max-width: 500px;
    width: 100%;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}


.dialog-content {
    text-align: center;
    margin-bottom: 20px;
}


.file-input {
    display: block;
    margin: 20px auto;
    padding: 10px;
    width: 100%;
    max-width: 300px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
}


.custom-footer {
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
}


.cancel-btn {
    background-color: #ff4d4f;
    border-color: #ff4d4f;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
}

.cancel-btn:hover {
    background-color: #ff7875;
    border-color: #ff7875;
}

.import-btn {
    background-color: #1890ff;
    border-color: #1890ff;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
}

.import-btn:hover {
    background-color: #40a9ff;
    border-color: #40a9ff;
}
</style>
