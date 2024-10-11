<script  setup>

import { useDoctorStore } from '../../../stores/store-doctors'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import {onBeforeMount} from "vue";

const confirm = useConfirm();
const toast = useToast();
const currency_sign = '$';

//define your time intervals here in order to update the range list
const time_range = [
    '09:00 AM-12:00 PM',
    '12:00 PM-03:00 PM',
    '03:00 PM-06:00 PM',
    '06:00 PM-09:00 PM',
    '09:00 PM-12:00 AM'
];

//define your price intervals here in order to update the range list
const price_range = [
    '0-20',
    '20-40',
    '40-60',
    '60-80',
    '80-100'
];

const price_range_with_currency = price_range.map(range => {
    const [min, max] = range.split('-');
    return `${currency_sign.concat(min)}-${currency_sign.concat(max)}`;
});
const store = useDoctorStore();

onBeforeMount(() => {
    store.getSpecializationList();
});
</script>

<template>
    <div class="col-3" v-if="store.quick_filters_doctors">

        <Panel class="is-small">

            <template class="p-1" #header>

                <div class="flex flex-row">
                    <div >
                        <b class="mr-1">Doctor Filters</b>
                    </div>

                </div>

            </template>

            <template #icons>

                <div class="p-inputgroup">

                    <Button data-testid="doctors-hide-filter"
                            class="p-button-sm"
                            @click="store.quick_filters_doctors = false">
                        <i class="pi pi-times"></i>
                    </Button>

                </div>

            </template>

            <VhFieldVertical >
                <template #label>
                    <b>Specialization:</b>
                </template>

                <div v-for="(specialization,index) in store.specializations" :key="index" class="field-radiobutton">
                    <RadioButton name="active-all"
                                 :inputId="specialization"
                                 :value="specialization"
                                 v-model="store.query.field_filter.specialization" />
                    <label for="active-all" class="cursor-pointer">{{specialization}}</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Price:</b>
                </template>

                <div v-for="(price,index) in price_range_with_currency" :key="index" class="field-radiobutton">
                    <RadioButton name="active-all"
                                 :inputId="price"
                                 :value="price"
                                 data-testid="doctors-filters-active-all"
                                 v-model="store.query.field_filter.price" />
                    <label for="active-all" class="cursor-pointer">{{price}}</label>
                </div>
            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Timings:</b>
                </template>

                <div v-for="(timing,index) in time_range" :key="index" class="field-radiobutton">
                    <RadioButton name="active-all"
                                 :inputId="timing"
                                 :value="timing"
                                 data-testid="doctors-filters-active-all"
                                 v-model="store.query.field_filter.timings" />
                    <label for="active-all" class="cursor-pointer">{{timing}}</label>
                </div>

            </VhFieldVertical>


        </Panel>

    </div>
</template>
