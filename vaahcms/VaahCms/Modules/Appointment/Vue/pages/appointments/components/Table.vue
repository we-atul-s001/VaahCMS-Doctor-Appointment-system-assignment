<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAppointmentStore } from '../../../stores/store-appointments'

const store = useAppointmentStore();
const useVaah = vaah();
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

             <Column field="patient" header="Patient Name"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.patient?.name || 'N/A'}}
                 </template>

             </Column>
             <Column field="doctor" header="Doctor Name"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.doctor?.name || 'N/A'}}
                 </template>

             </Column>
             <Column field="date" header="Date and Slot"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data?.date}} at {{formatTimeWithAmPm(prop.data.slot_start_time)}}
                 </template>

             </Column>

             <Column field="status" header="Status"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     <div>
                         <Badge style="width: 80px; display: flex;  justify-content:center; align-items:center;" :severity="prop.data.status === 1 ? 'success' : 'danger'">
                             {{ prop.data.status === 1 ? 'Booked' : 'Cancelled'}}
                         </Badge>
                     </div>
                 </template>


             </Column>

             <Column

                     field="reason"
                     header="Reason"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{ prop.data?.reason}}
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
                                 data-testid="appointments-table-is-active"
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
                                data-testid="appoinments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="appoinments-table-to-edit"
                                v-if="!(prop.data.status === 1 && store.hasPermission(store.assets.permission, 'appointment-has-access-of-patient') && store.hasPermission(store.assets.permission, 'appointment-has-access-of-doctor')) && prop.data.status !== 2 && store.hasPermission(store.assets.permission, 'appointment-has-access-of-patient')"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />




                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="doctors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && store.hasPermission(store.assets.permission, 'appointment-has-access-of-patient')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="appoinments-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && prop.data.status !== 0 && prop.data.status !== 2 && store.hasPermission(store.assets.permission, 'appointment-has-access-of-patient')"
                                @click="store.confirmToCancelAppointment( prop.data)"
                                v-tooltip.top="'Cancel Appointment'"
                                icon="pi pi-times" />

                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="appoinments-table-action-restore"
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

                    <template v-if="store.appointment_errors_display && store.appointment_errors_display.length > 0">
                        <tr v-for="(appointment_error, index) in store.appointment_errors_display" :key="'email-'+index">
                            <td>Appointment Error</td>
                            <td>{{ appointment_error }}</td>
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
                        <td><strong>Total Email Error:</strong></td>
                        <td>{{ store.email_errors_display ? store.email_errors_display.length : 0 }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Header Missing:</strong></td>
                        <td>{{ store.missing_fields_header ? store.missing_fields_header.length : 0 }}</td>
                    </tr>

                    <tr>
                        <td><strong>Total Appointment Errors:</strong></td>
                        <td>{{ store.appointment_errors_display ? store.appointment_errors_display.length : 0 }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </Dialog>

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

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
