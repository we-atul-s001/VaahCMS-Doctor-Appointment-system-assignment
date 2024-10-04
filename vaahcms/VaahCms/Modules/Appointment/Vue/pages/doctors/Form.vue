<script setup>
import { computed, onMounted, ref } from "vue";
import { useDoctorStore } from '../../stores/store-doctors';
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue';
import { useRoute } from 'vue-router';

const store = useDoctorStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if ((!store.item || Object.keys(store.item).length < 1) && route.params && route.params.id) {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu


const convertTo12HourFormat = (time) => {
    if (!time) return '';

    const [hours, minutes] = time.split(':').map(Number);
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const formattedHours = hours % 12 || 12;
    return `${formattedHours}:${minutes < 10 ? '0' + minutes : minutes} ${ampm}`;
};

const formattedShiftStartTime = computed({
    get() {
        return convertTo12HourFormat(store.item?.shift_start_time);
    },
    set(value) {
        store.item.shift_start_time = value;
    }
});

const formattedShiftEndTime = computed({
    get() {
        return convertTo12HourFormat(store.item?.shift_end_time);
    },
    set(value) {
        store.item.shift_end_time = value;
    }
});

</script>

<template>
    <div class="col-6">
        <Panel class="is-small">
            <template class="p-1" #header>
                <div v-if="store.assets.permission.includes('appointment-has-access-of-doctor')" class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>
                </div>
            </template>

            <template #icons>
                <div class="p-inputgroup">
                    <Button class="p-button-sm"
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="doctors-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="doctors-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="doctors-create-and-new"
                            icon="pi pi-save"/>

                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="doctors-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="doctors-to-list"
                            @click="store.toList()">
                    </Button>
                </div>
            </template>

            <div v-if="store.item" class="mt-2">
                <Message severity="error"
                         class="p-container-message mb-3"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">
                    <div class="flex align-items-center justify-content-between">
                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>
                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>
                    </div>
                </Message>

                <VhField label="Name">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Enter the name"
                                   name="doctors-name"
                                   data-testid="doctors-name"
                                   @update:modelValue="store.watchItem"
                                   v-model="store.item.name" required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Email">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Enter the Email"
                                   name="doctors-email"
                                   data-testid="doctors-email"
                                   v-model="store.item.email" required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Phone">
                    <div class="p-inputgroup">
                        <InputNumber class="w-full"
                                     placeholder="Enter the Phone"
                                     name="doctors-phone"
                                     :useGrouping="false"
                                     data-testid="doctors-phone"
                                     v-model="store.item.phone" required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Specialization">
                    <div class="p-inputgroup">
                        <InputText class="w-full"
                                   placeholder="Enter the Specialization"
                                   name="doctors-specialization"
                                   data-testid="doctors-specialization"
                                   v-model="store.item.specialization" required/>
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Working Hour">
                    <div class="p-inputgroup">
                        <Calendar
                            v-model="formattedShiftStartTime"
                            showTime
                            hourFormat="12"
                            time-only
                            placeholder="Shift Start Time"
                        />
                        <Calendar
                            v-model="formattedShiftEndTime"
                            showTime
                            hourFormat="12"
                            time-only
                            placeholder="Shift End Time"
                        />
                        <div class="required-field hidden"></div>
                    </div>
                </VhField>

                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="doctors-active"
                                 data-testid="doctors-active"
                                 v-model="store.item.is_active"/>
                </VhField>
            </div>
        </Panel>
    </div>
</template>
