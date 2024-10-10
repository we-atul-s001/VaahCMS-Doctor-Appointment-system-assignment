<template>
    <div style="margin-top: 8px;">
        <!-- Dashboard Title -->
        <h1 class="text-4xl">Dashboard</h1>

        <!-- Card Section with Equal Spacing -->
        <section style="margin-top: 20px;">
            <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between;">
                <!-- Card 1 -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Inventories
                    </div>
                    <div class="card-body">
                        <h2>999,000</h2>
                        <span class="growth-text">↑ +45.20 /month</span>
                        <div class="chart-placeholder"></div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Cost of Inventories
                    </div>
                    <div class="card-body">
                        <h2>$499,250</h2>
                        <span class="growth-text">↑ +45.20 /month</span>
                        <div class="chart-placeholder"></div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Total Items Reserved
                    </div>
                    <div class="card-body">
                        <h2>399,150</h2>
                        <span class="growth-text">↑ +45.20 /month</span>
                        <div class="chart-placeholder"></div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="stat-card">
                    <div class="card-header">
                        <i class="pi pi-file" style="margin-right: 8px;"></i>
                        Low Inventories
                        <button class="view-button">View</button>
                    </div>
                    <div class="card-body">
                        <h2>5,100</h2>
                        <span class="decline-text">↓ -452 /month</span>
                        <div class="chart-placeholder"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Chart Section -->
        <section style="margin-top: 40px;">
            <h2 class="text-2xl">Chart Section</h2>
            <div>
                <Chart type="bar" :data="chartData" :options="chartOptions" />
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import 'primeicons/primeicons.css';

// Chart data and options
const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

// Chart data configuration
const setChartData = () => {
    return {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [
            {
                label: 'Sales',
                data: [540, 325, 702, 620],
                backgroundColor: [
                    'rgba(249, 115, 22, 0.2)',
                    'rgba(6, 182, 212, 0.2)',
                    'rgba(107, 114, 128, 0.2)',
                    'rgba(139, 92, 246, 0.2)'
                ],
                borderColor: [
                    'rgb(249, 115, 22)',
                    'rgb(6, 182, 212)',
                    'rgb(107, 114, 128)',
                    'rgb(139, 92, 246)'
                ],
                borderWidth: 1
            }
        ]
    };
};

// Chart options configuration
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
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
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
};
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

.card-body h2 {
    font-size: 2rem;
    margin: 0;
}

.growth-text {
    color: #22c55e; /* Green for growth */
    margin-bottom: 10px;
}

.decline-text {
    color: #f43f5e; /* Red for decline */
    margin-bottom: 10px;
}

.chart-placeholder {
    height: 40px;
    background: #e5e7eb;
    border-radius: 5px;
}

.view-button {
    background-color: #e5e7eb;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.8rem;
    color: #424242;
    margin-left: auto;
    cursor: pointer;
}

.view-button:hover {
    background-color: #d4d4d8;
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
