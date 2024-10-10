
<script setup>
import {ref, onMounted} from 'vue';
import 'primeicons/primeicons.css';
import {vaah} from '../../vaahvue/pinia/vaah';
import {useRootStore} from '../../stores/root'


const total_doctors = ref(0);
const total_patients = ref(0);
const total_booked_appointments = ref(0);
const total_cancelled_appointments = ref(0);
const total_rescheduled_appointments = ref(0);

const useDashboardStore = useRootStore();

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    fetchDashboardData();
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});


const fetchDashboardData = async () => {
    try {
        const ajax_url = useDashboardStore.ajax_url;

        const response = await vaah().ajax(ajax_url+'/doctors/doctor-count');

    total_doctors.value = response.data.totalDoctors;

        total_patients.value = response.data.totalPatients;

        total_booked_appointments.value = response.data.totalBookedAppointments ;
        total_cancelled_appointments.value = response.data.totalCancelledAppointments;
        total_rescheduled_appointments.value = response.data.totalRescheduledAppointments;

    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
};

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    return {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [
            {
                label: 'First Dataset',
                data: [65, 59, 80, 81, 56, 55, 40],
                fill: false,
                borderColor: documentStyle.getPropertyValue('--p-cyan-500'),
                tension: 0.4
            },
            {
                label: 'Second Dataset',
                data: [28, 48, 40, 19, 86, 27, 90],
                fill: false,
                borderColor: documentStyle.getPropertyValue('--p-gray-500'),
                tension: 0.4
            }
        ]
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        maintainAspectRatio: false,
        aspectRatio: 0.6,
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
}
</script>

<style scoped>
/* Card Styling */
.stat-card {
    flex: 1;
    min-width: 250px;
    max-width: 300px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    font-size: 1rem;
    color: #424242;
    margin-bottom: 10px;
}

.card-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-body h1 {
    font-size: 2rem;
    margin: 0;
}

/* Section Styling */
section {
    padding: 20px;
    background-color: #f9fafb;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    margin-bottom: 20px;
}
</style>
<template>
    <div style="margin-top: 8px;">
        <!-- Dashboard Title -->
        <h1 className="text-4xl">Dashboard</h1>

        <!-- Card Section with Equal Spacing -->
        <section style="margin-top: 20px;">
            <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between;">
                <!-- Card 1: Total Doctors -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Doctors
                    </div>
                    <div class="card-body">
                        <h1>{{ total_doctors }}</h1>
                    </div>
                </div>

                <!-- Card 2: Total Patients -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Patients
                    </div>
                    <div class="card-body">
                        <h1>{{ total_patients }}</h1>
                    </div>
                </div>

                <!-- Card 3: Total Booked Appointments -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Booked Appointments
                    </div>
                    <div class="card-body">
                        <h1>{{ total_booked_appointments }}</h1>
                    </div>
                </div>

                <!-- Card 4: Total Cancelled Appointments -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Cancelled Appointments
                    </div>
                    <div class="card-body">
                        <h1>{{ total_cancelled_appointments }}</h1>
                    </div>
                </div>
                <!-- Card 5: Total Reschedule Appointments -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Reschedule Appointments
                    </div>
                    <div class="card-body">
                        <h1>{{ total_rescheduled_appointments }}</h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chart Section -->
        <section style="margin-top: 40px;">
            <h2 className="text-2xl">Chart Section</h2>
            <div class="card">
                <Chart type="line" :data="chartData" :options="chartOptions" class="h-[30rem]" />
            </div>
        </section>
    </div>
</template>


