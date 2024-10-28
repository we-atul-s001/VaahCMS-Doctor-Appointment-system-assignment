<script setup>
import { useDoctorStore } from '../../../stores/store-doctors';
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { onBeforeMount, computed } from "vue";

const confirm = useConfirm();
const toast = useToast();
const currency_sign = '₹';

const price_range = [
    '0-100',
    '100-200',
    '200-300',
    '300-400',
    '400-500',
];
const time_range = [
    '09:00 AM-12:00 PM',
    '12:00 PM-03:00 PM',
    '03:00 PM-06:00 PM',
    '06:00 PM-09:00 PM',
    '09:00 PM-12:00 AM'
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
                    <div>
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

            <!-- Specialization Section -->
            <VhFieldVertical>
                <template #label>
                    <b>Specialization:</b>
                </template>




                <div v-for="(item, index) in store.specializations" :key="index" class="field-checkbox">
                    <Checkbox :name="'specialization-' + index"
                              :inputId="item.specialization"
                              :value="item.specialization"
                              v-model="store.query.field_filter.specialization" />
                    <label :for="item.specialization" class="cursor-pointer">
                        {{ item.specialization }}
                    </label>
                    <Badge v-if="item.doctor_count > 0" :value="item.doctor_count" class="specialization-badge" />
                </div>



            </VhFieldVertical>

            <Divider />

            <!-- Price Section -->
            <VhFieldVertical>
                <template #label>
                    <b>Price:</b>
                </template>

                <div v-for="(price, index) in price_range_with_currency" :key="index" class="field-radiobutton">
                    <RadioButton name="price"
                              :inputId="price"
                              :value="price"
                              data-testid="doctors-filters-price"
                              v-model="store.query.field_filter.price" />
                    <label :for="price" class="cursor-pointer">{{ price }}</label>
                </div>
            </VhFieldVertical>

            <Divider />

            <!-- Timings Section -->
            <VhFieldVertical>
                <template #label>
                    <b>Timings:</b>
                </template>

                <div v-for="(timing, index) in time_range" :key="index" class="field-radiobutton">
                    <RadioButton name="timing"
                              :inputId="timing"
                              :value="timing"
                              data-testid="doctors-filters-timings"
                              v-model="store.query.field_filter.timings" />
                    <label :for="timing" class="cursor-pointer">{{ timing }}</label>
                </div>

            </VhFieldVertical>


        </Panel>

    </div>
</template>
<style>

.field-checkbox {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-bottom: 1rem;
}

.specialization-badge {
    margin-left: 0.1rem;
}
</style>
