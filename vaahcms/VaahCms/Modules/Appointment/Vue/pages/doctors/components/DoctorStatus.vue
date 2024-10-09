<script  setup>

import { useDoctorStore } from '../../../stores/store-doctors'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import { ref, watch } from 'vue';
import {vaah} from "../../../vaahvue/pinia/vaah";

const store = useDoctorStore();
const show_preferred = ref(false);

watch(() => store.show_status_panel, (newValue) => {
    if (!newValue) {
        show_preferred.value = false;
    }
})
</script>
<template>
    <Sidebar
        v-model:visible="store.show_status_panel"
        header="Doctor Appointment Status"
        position="right"
        style="width: 800px; font-size: xx-large"
    >
        <p v-if="store.appointments_count === 0">No records found.</p>
<TabView v-else>
    <TabPanel :header="'Booked (' + store.appointments_count + ')'">
        <DataTable v-if="store.item " style="border: 1px solid #ccc;margin-top:20px;"
                   :rows="20"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows">
            <Column header="Sr No" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{ props.index + 1 }}
                </template>
            </Column>
            <Column field="name" header="Patient Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <div  >
                        {{props.data.name}}
                        <span v-if="props.data.is_default === 1">
                         <Badge severity="info">&nbsp;(Default)</Badge>
                     </span>
                    </div>
                </template>
            </Column>
            <Column header="Doctor Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge :severity="props.data.quantity === 0 ? 'danger' : 'info'">
                        {{ props.data.quantity }}
                    </Badge>
                </template>
            </Column>

            <Column header="Appointment" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge :severity="props.data.quantity === 0 ? 'danger' : 'info'">
                        {{ props.data.quantity }}
                    </Badge>
                </template>
            </Column>

            <Column field="price_per_minutes" header="Price" style="border: 1px solid #ccc;">
                <template #body="props">
                    <template v-if="props.data.product_price_range.length">
                        <Badge severity="info">
                            {{ store.getPriceRangeOfProduct(props.data.product_price_range) }}
                        </Badge>
                    </template>
                    <template v-else>
                        <span >0</span>
                    </template>
                </template>
            </Column>


            <template #empty="prop">

                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

            </template>
        </DataTable>
        </TabPanel>

    <TabPanel header="Cancelled">
        <DataTable v-if="store.item " style="border: 1px solid #ccc;margin-top:20px;"
                   :rows="20"
                   :paginator="true"
                   class="p-datatable-sm p-datatable-hoverable-rows">
            <Column header="Sr No" style="border: 1px solid #ccc;">
                <template #body="props">
                    {{ props.index + 1 }}
                </template>
            </Column>
            <Column field="name" header="Patient Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <div  >
                        {{props.data.name}}
                        <span v-if="props.data.is_default === 1">
                         <Badge severity="info">&nbsp;(Default)</Badge>
                     </span>
                    </div>
                </template>
            </Column>
            <Column header="Doctor Name" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge :severity="props.data.quantity === 0 ? 'danger' : 'info'">
                        {{ props.data.quantity }}
                    </Badge>
                </template>
            </Column>

            <Column header="Appointment" style="border: 1px solid #ccc;">
                <template #body="props">
                    <Badge :severity="props.data.quantity === 0 ? 'danger' : 'info'">
                        {{ props.data.quantity }}
                    </Badge>
                </template>
            </Column>

            <Column field="price_per_minutes" header="Price" style="border: 1px solid #ccc;">
                <template #body="props">
                    <template v-if="props.data.product_price_range.length">
                        <Badge severity="info">
                            {{ store.getPriceRangeOfProduct(props.data.product_price_range) }}
                        </Badge>
                    </template>
                    <template v-else>
                        <span >0</span>
                    </template>
                </template>
            </Column>


            <template #empty="prop">

                <div  style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

            </template>
        </DataTable>
    </TabPanel>
</TabView>
    </Sidebar>
</template>


