<script setup>
import {onMounted, ref, watch} from "vue";
import { useAppointmentStore } from '../../stores/store-appointments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useAppointmentStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
            && route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
});
function formatTimeWithAmPm(time) {
    if (!time) return '';

    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    const amPm = date.getHours() >= 12 ? 'PM' : 'AM';

    let hour = date.getHours() % 12;
    if (hour === 0) hour = 12;

    return `${hour}:${minutes} ${amPm}`;
}
//--------form_menu
const handleDateChange = (newDate, property) => {
    if (newDate && store.item[property] !== undefined) {
        store.item[property] = new Date(newDate.getTime() - newDate.getTimezoneOffset() * 60000);
    }
    console.log(property, store.item[property]);
};
//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu
const today_date = ref(new Date());
const isValidTime = (date) => date instanceof Date && !isNaN(date.getTime());

</script>
<template>

    <div class="col-6" >

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
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
                            data-testid="appointments-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="appointments-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="appointments-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="appointments-form-menu"
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



                <VhField label="Select Patient">
                    <Dropdown
                        filter
                        name="appointments-patient"
                        data-testid="items-patient"
                        placeholder="Select Patient"
                        :options="store.assets.patients"
                        v-model="store.item.patient_id"
                        option-label="name"
                        class="w-full"
                        showClear
                        option-value="id"
                    />
                </VhField>
                <VhField label="Select Doctor">

                    <Dropdown

                        filter
                        name="appointments-doctor"
                        data-testid="items-doctor"
                        placeholder="Select Doctor"
                        :options="store.assets.doctors"
                        v-model="store.item.doctor"
                        option-label="name"
                        class="w-full"
                        showClear
                    />
                </VhField>
                <VhField  label="Doctor Details"  v-if="store.item.doctor" >

                    <b>Specialization
                    </b>- {{store.item.doctor?.specialization}}<br>
                    <b>
                        Shift Time-</b>

                    {{store.item?.doctor?.shift_start_time}} -
                    {{store.item?.doctor?.shift_end_time}}
                    (Please Select the time in the given time slot).
                    <b>
                        Price Per 30 Minutes Slot-</b>

                    ₹ {{store.item?.doctor?.price_per_minutes}}
                </VhField>
                <VhField label="Date and Time" required>
                    <div class="p-inputgroup">
                        <Calendar
                            name="appointments-date"
                            date-format="yy-mm-dd"
                            :showIcon="true"
                            :minDate="today_date"
                            data-testid="items-date"
                            @date-select="handleDateChange($event,'date')"
                            v-model="store.item.date"
                            :pt="{
                                  monthPicker:{class:'w-15rem'},
                                  yearPicker:{class:'w-15rem'}
                              }"
                            placeholder="Select Appointment Date"
                        />
                        <Calendar
                            v-model="store.item.slot_start_time"
                            :pt="{
                                  monthPicker:{class:'w-15rem'},
                                  yearPicker:{class:'w-15rem'}
                              }"
                            time-only

                            hourFormat="12"
                            stepMinute="30"
                            :showIcon="true"
                            :inputStyle="{ pointerEvents: 'none' }"
                            placeholder="Appointment Time"
                        />
                    </div>
                </VhField>
                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="appointments-active"
                                 data-testid="appointments-active"
                                 v-model="store.item.is_active"/>
                </VhField>


            </div>
        </Panel>

    </div>

</template>
