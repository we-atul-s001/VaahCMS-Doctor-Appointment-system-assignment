<script setup>
import {ref, onMounted} from 'vue';
import 'primeicons/primeicons.css';
import {vaah} from '../../vaahvue/pinia/vaah';
import {useRootStore} from '../../stores/root';

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
});

const fetchDashboardData = async () => {
    try {
        const ajax_url = useDashboardStore.ajax_url;
        const response = await vaah().ajax(ajax_url + '/doctors/doctor-count');

        total_doctors.value = response.data.totalDoctors;
        total_patients.value = response.data.totalPatients;
        total_booked_appointments.value = response.data.totalBookedAppointments;
        total_cancelled_appointments.value = response.data.totalCancelledAppointments;
        total_rescheduled_appointments.value = response.data.totalRescheduledAppointments;

        chartData.value = setChartData();
        chartOptions.value = setChartOptions();
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
};

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    return {
        labels: ['Doctors', 'Patients', 'Booked Appointments', 'Cancelled Appointments', 'Rescheduled Appointments'],
        datasets: [
            {
                label: 'Doctor Appointment System',
                data: [
                    total_doctors.value,
                    total_patients.value,
                    total_booked_appointments.value,
                    total_cancelled_appointments.value,
                    total_rescheduled_appointments.value,
                ],
                fill: true,
                backgroundColor: [
                    '#B0BEC5',
                    '#90A4AE',
                    '#78909C',
                    '#607D8B',
                    '#455A64'
                ],
                borderColor: [
                    '#78909C',
                    '#546E7A',
                    '#37474F',
                    '#263238',
                    '#1C1C1C'
                ],

                borderWidth: 1,
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
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            },
            tooltip: {
                callbacks: {
                    label: (context) => {

                        const label = context.label || '';
                        const value = context.raw;
                        return `${label}: ${value}`;
                    }
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
                },
                beginAtZero: true
            }
        }
    };
};

</script>

<template>
    <div style="margin-top: 8px;">
        <!-- Dashboard Title -->
        <h1 className="text-4xl">Dashboard</h1>

        <!-- Card Section with Equal Spacing -->

        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; margin-top: 25px">
            <!-- Card 1: Total Doctors -->
            <div className="stat-card">
                <div className="card-header">
                    <i className="pi pi-file" style="margin-right: 8px;"></i>
                    Total Doctors
                </div>
                <div className="card-body">
                    <h1>{{ total_doctors }}</h1>
                </div>
            </div>

            <!-- Card 2: Total Patients -->
            <div className="stat-card">
                <div className="card-header">
                    <i className="pi pi-file" style="margin-right: 8px;"></i>
                    Total Patients
                </div>
                <div className="card-body">
                    <h1>{{ total_patients }}</h1>
                </div>
            </div>

            <!-- Card 3: Total Booked Appointments -->
            <div className="stat-card">
                <div className="card-header">
                    <i className="pi pi-file" style="margin-right: 8px;"></i>
                    Total Booked Appointments
                </div>
                <div className="card-body">
                    <h1>{{ total_booked_appointments }}</h1>
                </div>
            </div>

            <!-- Card 4: Total Cancelled Appointments -->
            <div className="stat-card">
                <div className="card-header">
                    <i className="pi pi-file" style="margin-right: 8px;"></i>
                    Total Cancelled Appointments
                </div>
                <div className="card-body">
                    <h1>{{ total_cancelled_appointments }}</h1>
                </div>
            </div>

            <!-- Card 5: Total Rescheduled Appointments -->
            <div className="stat-card">
                <div className="card-header">
                    <i className="pi pi-file" style="margin-right: 8px;"></i>
                    Total Rescheduled Appointments
                </div>
                <div className="card-body">
                    <h1>{{ total_rescheduled_appointments }}</h1>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <section style="margin-top: 40px;">
            <h2 className="text-2xl">Overview</h2>
            <div className="card">
                <Chart type="bar" :data="chartData" :options="chartOptions" class="h-[30rem]"/>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Same styling as before */
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
