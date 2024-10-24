<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah';
import { useDoctorStore } from '../../../stores/store-doctors';
import { ref, computed } from 'vue';

const store = useDoctorStore();
const useVaah = vaah();

const visible_right = ref(false);
const current_appointment_count = ref(0);
const current_cancelled_appointment_count = ref(0);
const appointment_details = ref([]);
const booked_appointments = ref([]);
const cancelled_appointments = ref([]);

function openSidebar(appointmentsCount, appointmentsList) {
    current_appointment_count.value = appointmentsCount;
    appointment_details.value = appointmentsList;

    booked_appointments.value = appointmentsList.filter(appointment => appointment.status === 1);
    cancelled_appointments.value = appointmentsList.filter(appointment => appointment.status === 0 || appointment.status === 2);


    current_cancelled_appointment_count.value = cancelled_appointments.value.length;

    visible_right.value = true;
}
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
</script>

<template>
    <div v-if="store.list">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

            <Column field="name" header="Name"
                    class="overflow-wrap-anywhere"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{prop.data.name}}
                </template>

            </Column>

            <Column field="email" header="Email"
                    class="overflow-wrap-anywhere"
                    style="width:500px;"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.email}}
                </template>

            </Column>

            <Column field="phone" header="Phone"
                    class="overflow-wrap-anywhere"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.phone}}
                </template>

            </Column>

            <Column field="specialization" header="Specialization"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    {{prop.data.specialization}}
                </template>

            </Column>

            <Column field="shift_start_time" header="Shift Start Time"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{ formatTimeWithAmPm(prop.data.shift_start_time) }}
                </template>

            </Column>

            <Column field="shift_end_time" header="Shift End Time"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{ formatTimeWithAmPm(prop.data.shift_end_time) }}
                </template>

            </Column>

            <Column field="price_per_session" header="Price per Session"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">
                <template #body="prop">
                    <div style="position: relative; display: inline-block;">
                        ₹ {{ prop.data.price_per_session }}
                        <badge severity="info"
                               style="position: absolute; top: -10px; right: -28px; font-size: 12px;
                                color: #fff; border-radius: 50%; padding: 5px 8px; height: 24px; width: 24px;
                                display: flex; justify-content: center; align-items: center;"
                               @click="openSidebar(prop.data.appointments_count, prop.data.appointments_list)">
                            {{ prop.data.appointments_count || 0 }}
                        </badge>
                    </div>
                </template>
            </Column>

            <Column field="updated_at" header="Updated"
                    v-if="store.isViewLarge()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{useVaah.strToSlug(prop.data.updated_at)}}
                </template>

            </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="doctors-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="doctors-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="doctors-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="doctors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && store.hasPermission(store.assets.permission, 'appointment-has-access-of-patient')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />

                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="doctors-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />
                    </div>
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-3">
                    No records found.
                </div>
            </template>

        </DataTable>
        <!--/table-->

        <!--Sidebar-->
        <Sidebar v-model:visible="visible_right"
                 header="Doctor Appointment Details"
                 position="right"
                 style="width: 800px; font-size: 25px; overflow-x: hidden;">

            <TabView>
                <!-- Booked Tab -->
                <TabPanel :header="'Booked (' + current_appointment_count + ')'">
                    <DataTable :value="booked_appointments" dataKey="id" class="p-datatable-sm p-datatable-hoverable-rows">
                        <Column field="id" header="ID" :sortable="true" :style="{ width: '80px' }">
                            <template #body="prop">
                                {{ prop.data.id }}
                            </template>
                        </Column>

                        <Column field="patient_name" header="Patient Name"
                                class="overflow-wrap-anywhere"
                                style="width:150px;"
                                :sortable="true">
                            <template #body="prop">
                                {{ prop.data.patient_name }}
                            </template>
                        </Column>




                        <Column field="price_per_session" header="Price per Session"
                                class="overflow-wrap-anywhere"
                                style="width:150px;"
                                :sortable="true">
                            <template #body="prop">
                                ₹ {{ prop.data.price_per_session }}
                            </template>
                        </Column>

                        <Column field="date" header="Appointment Date" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.date }}
                            </template>
                        </Column>

                        <Column field="time" header="Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(prop.data.slot_start_time) }}
                            </template>
                        </Column>

                        <Column field="status" header="Status" :sortable="true">
                            <template #body="prop">
                                <Badge v-if="prop.data.status === 1" value="Booked" severity="success"></Badge>
                            </template>
                        </Column>
                        <template #empty="prop">
                            <div style="text-align: center; font-size: 12px; color: #888;">No records found.</div>
                        </template>
                    </DataTable>
                </TabPanel>

                <!-- Cancelled Tab -->
                <TabPanel :header="'Cancelled (' + current_cancelled_appointment_count + ')'">
                    <DataTable :value="cancelled_appointments" dataKey="id" class="p-datatable-sm p-datatable-hoverable-rows">
                        <Column field="id" header="ID" :sortable="true" :style="{ width: '80px' }">
                            <template #body="prop">
                                {{ prop.data.id }}
                            </template>
                        </Column>

                        <Column field="patient_name" header="Patient Name"
                                class="overflow-wrap-anywhere"
                                style="width:150px;"
                                :sortable="true">
                            <template #body="prop">
                                {{ prop.data.patient_name }}
                            </template>
                        </Column>



                        <Column field="price_per_session" header="Price per Session"
                                class="overflow-wrap-anywhere"
                                style="width:150px;"
                                :sortable="true">
                            <template #body="prop">
                                ₹ {{ prop.data.price_per_session }}
                            </template>
                        </Column>

                        <Column field="date" header="Appointment Date" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.date }}
                            </template>
                        </Column>

                        <Column field="time" header="Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(prop.data.slot_start_time) }}
                            </template>
                        </Column>

                        <Column field="status" header="Status" :sortable="true">
                            <template #body="prop">
                                <Badge v-if="prop.data.status === 0" value="Rescheduled" severity="info"></Badge>
                                <Badge v-else-if="prop.data.status === 2" value="Cancelled" severity="danger"></Badge>
                            </template>
                        </Column>
                        <template #empty="prop">
                            <div style="text-align: center; font-size: 12px; color: #888;">No records found.</div>
                        </template>
                    </DataTable>
                </TabPanel>
            </TabView>
        </Sidebar>
        <!--/Sidebar-->

        <Dialog
            v-model:visible="store.is_visible_errors"
            maximizable
            modal
            header="Duplicate Issues"
            :style="{ width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <div class="error-container">
                <table class="styled-table">
                    <thead>
                    <tr>
                        <th>Error Type</th>
                        <th>Message</th>
                    </tr>
                    </thead>
                    <tbody>

                    <!-- Email Errors -->
                    <template v-if="store.email_errors_display && store.email_errors_display.length > 0">
                        <tr v-for="(email_error, index) in store.email_errors_display" :key="'email-'+index">
                            <td>Email Error</td>
                            <td>{{ email_error }}</td>
                        </tr>
                    </template>

                    <template v-if="store.missing_fields_header && store.missing_fields_header.length > 0">
                        <tr v-for="(header_error, index) in store.missing_fields_header" :key="'email-'+index">
                            <td>Header Error</td>
                            <td>{{ header_error }}</td>
                        </tr>
                    </template>
                    </tbody>
                    <tfoot>
<tr>
                        <td><strong>Total Email Duplicate:</strong></td>
                        <td>{{ store.email_errors_display ? store.email_errors_display.length : 0 }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Header Missing:</strong></td>
                        <td>{{ store.missing_fields_header ? store.missing_fields_header.length : 0 }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </Dialog>
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
    </div>

</template>

<style scoped>
.error-container {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    font-family: 'Arial', sans-serif;
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    font-size: 0.9em;
    color: #333;
}

.styled-table thead tr {
    background-color: #6c757d;
    color: #fff;
    text-align: left;
}

.styled-table th, .styled-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
}

.styled-table tbody tr {
    border-bottom: 1px solid #ddd;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

.styled-table tfoot tr {
    font-weight: bold;
}

.styled-table td {
    color: #495057;
}

.styled-table tfoot td {
    background-color: #e9ecef;
    color: #495057;
}

</style>
