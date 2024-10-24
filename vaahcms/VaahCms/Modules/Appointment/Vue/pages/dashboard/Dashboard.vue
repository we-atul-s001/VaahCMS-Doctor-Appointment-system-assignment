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
const pieChartData = ref();
const pieChartOptions = ref();

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

        pieChartData.value = setPieChartData();
        pieChartOptions.value = setPieChartOptions();
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
};

const setChartData = () => {
    return {
        labels: ['Doctors', 'Patients'],
        datasets: [
            {
                label: 'Doctor Appointment System',
                data: [
                    total_doctors.value,
                    total_patients.value,
                ],
                fill: true,
                backgroundColor: ['#B0BEC5', '#90A4AE'],
                borderColor: ['#78909C', '#546E7A'],
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

const setPieChartData = () => {
    return {
        labels: ['Booked Appointments', 'Cancelled Appointments', 'Rescheduled Appointments'],
        datasets: [
            {
                data: [
                    total_booked_appointments.value,
                    total_cancelled_appointments.value,
                    total_rescheduled_appointments.value
                ],
                backgroundColor: [
                    '#90A4AE',
                    '#78909C',
                    '#607D8B'
                ],
                hoverBackgroundColor: [
                    '#B0BEC5',
                    '#90A4AE',
                    '#78909C'
                ]
            }
        ]
    };
};

const setPieChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');

    return {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    usePointStyle: true,
                    color: textColor
                }
            }
        }
    };
};
</script>

<template>
    <div style="margin-top: 8px;">
        <!-- Dashboard Title -->
        <h1 class="text-4xl">Dashboard</h1>

        <!-- Card Section -->
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; margin-top: 25px">
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

            <!-- Card 5: Total Rescheduled Appointments -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-file" style="margin-right: 8px;"></i>
                    Total Rescheduled Appointments
                </div>
                <div class="card-body">
                    <h1>{{ total_rescheduled_appointments }}</h1>
                </div>
            </div>
        </div>

        <!-- Bar Chart and Pie Chart Section in Two Equal Halves -->
        <section class="charts-section" style="margin-top: 40px;">
            <h2 class="text-2xl">Overview</h2>
            <div class="charts-container">
                <!-- Bar Chart -->
                <div class="chart-item">
                    <h3>Doctor & Patient Overview</h3>
                    <div class="card">
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="h-[30rem]"/>
                    </div>
                </div>
                <!-- Pie Chart -->
                <div class="chart-item">
                    <h3>Appointments Overview</h3>
                    <div class="card">
                        <Chart type="pie" :data="pieChartData" :options="pieChartOptions" class="h-[30rem]"/>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
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


h2 {
    margin-bottom: 20px;
}

.charts-section {
    padding: 20px;
    border-radius: 8px;
}

.charts-container {
    display: flex;
    justify-content: space-between;
    gap: 20px; /* Space between the two charts */
}

.chart-item {
    flex: 1;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>
