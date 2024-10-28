<script setup>
import { ref, onMounted } from 'vue';
import 'primeicons/primeicons.css';
import { vaah } from '../../vaahvue/pinia/vaah';
import { useRootStore } from '../../stores/root';
import Chart from 'primevue/chart';

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
                    color: textColor,
                    font: {
                        size: 14
                    }
                }
            },
            title: {
                display: true,
                text: 'Doctor & Patient Overview',
                font: {
                    size: 20
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
                    color: textColorSecondary,
                    font: {
                        size: 12
                    }
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        size: 12
                    }
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
                    color: textColor,
                    font: {
                        size: 14
                    }
                }
            },
            title: {
                display: true,
                text: 'Appointments Overview',
                font: {
                    size: 20
                }
            }
        }
    };
};
</script>

<template>
    <div class="dashboard">
        <!-- Dashboard Title -->
        <h1 class="dashboard-title">Dashboard</h1>

        <!-- Card Section -->
        <div class="stat-cards">
            <!-- Card 1: Total Doctors -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-user"></i>
                    Total Doctors
                </div>
                <div class="card-body">
                    <h1>{{ total_doctors }}</h1>
                </div>
            </div>

            <!-- Card 2: Total Patients -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-users"></i>
                    Total Patients
                </div>
                <div class="card-body">
                    <h1>{{ total_patients }}</h1>
                </div>
            </div>

            <!-- Card 3: Total Booked Appointments -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-calendar-plus"></i>
                    Total Booked Appointments
                </div>
                <div class="card-body">
                    <h1>{{ total_booked_appointments }}</h1>
                </div>
            </div>

            <!-- Card 4: Total Cancelled Appointments -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-calendar-times"></i>
                    Total Cancelled Appointments
                </div>
                <div class="card-body">
                    <h1>{{ total_cancelled_appointments }}</h1>
                </div>
            </div>

            <!-- Card 5: Total Rescheduled Appointments -->
            <div class="stat-card">
                <div class="card-header">
                    <i class="pi pi-calendar-minus"></i>
                    Total Rescheduled Appointments
                </div>
                <div class="card-body">
                    <h1>{{ total_rescheduled_appointments }}</h1>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <section class="charts-section">
            <h2 class="section-title">Overview</h2>
            <div class="charts-container">
                <!-- Bar Chart -->
                <div class="chart-item">
                    <div class="card">
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="chart" />
                    </div>
                </div>
                <!-- Pie Chart -->
                <div class="chart-item">
                    <div class="card">
                        <Chart type="pie" :data="pieChartData" :options="pieChartOptions" class="chart" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.dashboard {
    padding: 20px;
}

.dashboard-title {
    font-size: 2.5rem;
    margin-bottom: 30px;
}

.stat-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
    margin-bottom: 40px;
}

.stat-card {
    flex: 1;
    min-width: 200px;
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
    font-size: 1.1rem;
    color: #424242;
    margin-bottom: 15px;
}

.card-header i {
    margin-right: 10px;
    font-size: 1.3rem;
}

.card-body h1 {
    font-size: 2.5rem;
    margin: 0;
}

.section-title {
    font-size: 2rem;
    margin-bottom: 30px;
}

.charts-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.chart-item {
    width: 100%;
    border-radius: 8px;
    padding: 20px;
}

.chart {
    height: 400px;
}

@media (min-width: 1200px) {
    .charts-container {
        flex-direction: row;
    }

    .chart-item {
        width: calc(50% - 20px);
    }
}
</style>
